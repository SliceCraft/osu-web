<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Libraries\Score;

use App\Libraries\Search\ScoreSearch;
use App\Libraries\Search\ScoreSearchParams;
use App\Models\Solo\Score as SoloScore;
use Illuminate\Database\Eloquent\Collection;

class BeatmapScores
{
    public array $result;
    private ScoreSearchParams $baseParams;

    public function __construct(private array $rawParams)
    {
        $this->baseParams = ScoreSearchParams::fromArray(array_merge([
            'limit' => 50,
            'sort' => 'score_desc',
        ], $rawParams));
    }

    public function all(): Collection
    {
        $limit = $this->baseParams->size;
        $params = clone $this->baseParams;
        $params->size = $limit + 50;
        $search = new ScoreSearch($params);

        $nextCursor = null;
        $hasNext = true;
        $this->result = [];

        while ($hasNext) {
            if ($nextCursor !== null) {
                $search->searchAfter(array_values($nextCursor));
            }
            $search->response();
            $search->assertNoError();

            $this->append($search->records()->all());

            if (count($this->result) >= $limit) {
                break;
            }
            $nextCursor = $search->getSortCursor();
            $hasNext = $nextCursor !== null;
        }

        return new Collection(array_values($this->result));
    }

    public function rank(SoloScore $score): int
    {
        if (isset($this->result)) {
            $userId = $score->user_id;
            if (isset($this->result[$userId])) {
                $rank = 0;
                foreach ($this->result as $checkUserId => $score) {
                    $rank++;
                    if ($userId === $checkUserId) {
                        return $rank;
                    }
                }
            }
        }

        $params = clone $this->baseParams;
        $params->beforeScore = $score;
        $params->setSort(null);

        return UserRank::getRank($params);
    }

    public function userBest(): ?SoloScore
    {
        if (!isset($this->baseParams->user)) {
            return null;
        }

        $userId = $this->baseParams->user->getKey();

        if (isset($this->result[$userId])) {
            return $this->result[$userId];
        }

        $params = clone $this->baseParams;
        $params->size = 1;
        $params->userId = $userId;
        $params->setSort(null);
        $search = new ScoreSearch($params);

        $search->response();
        $search->assertNoError();

        return $search->records()[0] ?? null;
    }

    private function append(array $newScores): void
    {
        foreach ($newScores as $score) {
            $userId = $score->user_id;

            if (!isset($this->result[$userId])) {
                $this->result[$userId] = $score;

                if (count($this->result) >= $this->baseParams->size) {
                    return;
                }
            }
        }
    }
}