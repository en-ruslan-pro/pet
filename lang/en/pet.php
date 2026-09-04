<?php

return [
    'status' => [
        'default_name' => 'Pet',
        'label' => 'Pet needs',
    ],
    'actions' => [
        'idle' => 'Resting',
        'walk' => 'Walking',
        'look_window' => 'Looking out the window',
        'meow' => 'Meowing',
        'sit' => 'Sitting',
        'sleep' => 'Sleeping',
        'eat' => 'Eating',
        'play' => 'Playing',
        'scratch' => 'Scratching',
    ],
    'messages' => [
        'animation' => 'Animation: :name',
        'meowing' => ':name is meowing',
        'model_load_failed' => 'Could not load the pet model',
    ],
    'needs' => [
        'satiety' => 'Satiety',
        'energy' => 'Energy',
        'happiness' => 'Happiness',
    ],
];
