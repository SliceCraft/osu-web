<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace App\Models\Solo;

use App\Libraries\Score\UserRankCache;
use App\Models\Beatmap;
use App\Models\Model;
use App\Models\Score as LegacyScore;
use App\Models\User;
use App\Transformers\ScoreTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelRedis;

/**
 * @property int $beatmap_id
 * @property \Carbon\Carbon|null $created_at
 * @property \stdClass $data
 * @property \Carbon\Carbon|null $deleted_at
 * @property int $id
 * @property bool $preserve
 * @property int $ruleset_id
 * @property \Carbon\Carbon|null $updated_at
 * @property User $user
 * @property int $user_id
 */
class Score extends Model
{
    use SoftDeletes;

    const PROCESSING_QUEUE = 'osu-queue:score-statistics';

    protected $table = 'solo_scores';
    protected $casts = [
        'preserve' => 'boolean',
        'data' => ScoreData::class,
    ];

    public static function createFromJsonOrExplode(array $params)
    {
        $score = new static([
            'beatmap_id' => $params['beatmap_id'],
            'ruleset_id' => $params['ruleset_id'],
            'user_id' => $params['user_id'],
            'data' => $params,
        ]);

        $score->data->assertCompleted();

        // this should potentially just be validation rather than applying this logic here, but
        // older lazer builds potentially submit incorrect details here (and we still want to
        // accept their scores.
        if (!$score->data->passed) {
            $score->data->rank = 'D';
        }

        $score->saveOrExplode();

        return $score;
    }

    public function beatmap()
    {
        return $this->belongsTo(Beatmap::class, 'beatmap_id');
    }

    public function performance()
    {
        return $this->hasOne(ScorePerformance::class, 'score_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createLegacyEntryOrExplode()
    {
        $data = $this->data;
        $statistics = $data->statistics;
        $scoreClass = LegacyScore\Model::getClass($this->ruleset_id);

        $score = new $scoreClass([
            'beatmap_id' => $this->beatmap_id,
            'beatmapset_id' => $this->beatmap?->beatmapset_id ?? 0,
            'countmiss' => $statistics->miss,
            'enabled_mods' => app('mods')->idsToBitset(array_column($data->mods, 'acronym')),
            'maxcombo' => $data->maxCombo,
            'pass' => $data->passed,
            'perfect' => $data->passed && $statistics->miss + $statistics->largeTickMiss === 0,
            'rank' => $data->rank,
            'score' => $data->totalScore,
            'scorechecksum' => "\0",
            'user_id' => $this->user_id,
        ]);

        switch (Beatmap::modeStr($this->ruleset_id)) {
            case 'osu':
                $score->count300 = $statistics->great;
                $score->count100 = $statistics->ok;
                $score->count50 = $statistics->meh;
                break;
            case 'taiko':
                $score->count300 = $statistics->great;
                $score->count100 = $statistics->ok;
                break;
            case 'fruits':
                $score->count300 = $statistics->great;
                $score->count100 = $statistics->largeTickHit;
                $score->countkatu = $statistics->smallTickMiss;
                $score->count50 = $statistics->smallTickHit;
                break;
            case 'mania':
                $score->countgeki = $statistics->perfect;
                $score->count300 = $statistics->great;
                $score->countkatu = $statistics->good;
                $score->count100 = $statistics->ok;
                $score->count50 = $statistics->meh;
                break;
        }

        $score->saveOrExplode();

        return $score;
    }

    public function getMode(): string
    {
        return Beatmap::modeStr($this->ruleset_id);
    }

    /**
     * Queue the item for score processing
     *
     * @param array $dataJson Pre-generated json of the score so the transformer doesn't need to be redundantly run
     * @param bool $force By default only newly created scores are queued. Pass true to force queuing the score
     */
    public function queueForProcessing(?array $dataJson = null, bool $force = false): void
    {
        if (!$force && !$this->wasRecentlyCreated) {
            return;
        }

        $dataJson ??= json_item($this, new ScoreTransformer(ScoreTransformer::TYPE_SOLO));

        LaravelRedis::lpush(static::PROCESSING_QUEUE, json_encode([
            'Score' => [
                'beatmap_id' => $dataJson['beatmap_id'],
                'data' => json_encode($dataJson),
                'id' => $dataJson['id'],
                'user_id' => $dataJson['user_id'],
            ],
        ]));
    }

    public function userRank(): ?int
    {
        return UserRankCache::fetch([], $this->beatmap_id, $this->ruleset_id, $this->data->totalScore);
    }
}
