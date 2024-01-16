<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

return [
    'index' => [
        'description' => 'Valmiiksi pakattuja rytmikarttakokoelmia, joissa yhdistyy tietty teema.',
        'empty' => 'Tulossa pian!',
        'nav_title' => 'listaus',
        'title' => 'Beatmappipaketit',

        'blurb' => [
            'important' => 'LUE TÄMÄ ENNEN LATAAMISTA',
            'install_instruction' => 'Asennus: Kun paketti on latautunut, pura sen sisältö osu!n "Songs"-tiedostohakemistoon ja osu! hoitaa loput.',
            'note' => [
                '_' => 'Huomaa myös, että on erittäin suositeltavaa :scary, koska vanhemmat kartat ovat yleisellä tasolla heikompilaatuisia kuin uudemmat.',
                'scary' => 'ladata uusimpia kokoelmia vanhojen sijaan',
            ],
        ],
    ],

    'show' => [
        'download' => 'Lataa',
        'item' => [
            'cleared' => 'läpäisty',
            'not_cleared' => 'läpäisemätön',
        ],
        'no_diff_reduction' => [
            '_' => ':link ei voi käyttää tämän paketin suorittamiseen.',
            'link' => 'Vaikeusastetta vähentäviä muunnelmia',
        ],
    ],

    'mode' => [
        'artist' => 'Esittäjä/Albumi',
        'chart' => 'Kohdevaloissa',
        'featured' => 'Esitelty artisti',
        'loved' => 'Projekti Rakastettu',
        'standard' => 'Tavallinen',
        'theme' => 'Teema',
        'tournament' => 'Turnaus',
    ],

    'require_login' => [
        '_' => 'Sinun täytyy olla :link ladataksesi',
        'link_text' => 'kirjautuneena sisään',
    ],
];
