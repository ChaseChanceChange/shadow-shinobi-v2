<?php
return [
    'enhancement' => [
        'minimum_sacrifice_pieces' => 3,
        5 => ['success' => 0.92, 'destroy' => 0.00],
        10 => ['success' => 0.72, 'destroy' => 0.15],
        15 => ['success' => 0.45, 'destroy' => 0.35],
        16 => ['success' => 0.18, 'destroy' => 0.72],
    ],
    'world_memory' => [
        'rare_event_thresholds' => [
            'enhancement' => [11, 16, 20],
            'bosses' => [1, 10, 25],
        ],
    ],
];
