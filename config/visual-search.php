<?php

return [
    'url' => env('VISUAL_SEARCH_URL', 'http://127.0.0.1:8020'),
    'timeout' => (int) env('VISUAL_SEARCH_TIMEOUT', 120),
    'minimum_similarity' => (float) env('VISUAL_SEARCH_MIN_SIMILARITY', 0.45),
    'token' => env('VISUAL_SEARCH_TOKEN'),
];
