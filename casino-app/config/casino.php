<?php

return [
    'round' => [
        'betting_duration_seconds' => 30,
        'spin_duration_seconds' => 5,
    ],
    'bet_limits' => [
        'min' => 1,
        'max' => 1000,
    ],
    'payouts' => [
        'straight' => 35,
        'red' => 1,
        'black' => 1,
        'odd' => 1,
        'even' => 1,
        'dozen' => 2,
    ],
    'wheel' => [
        'numbers' => range(0, 36),
        'red' => [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36],
        'black' => [2,4,6,8,10,11,13,15,17,20,22,24,26,28,29,31,33,35],
    ],
];
