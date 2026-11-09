<?php

return [
    'openai_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
    'timeout' => 20,
    'daily_ai_limit' => (int) env('LAUNDRY_ASSISTANT_DAILY_AI_LIMIT', 200),
    'bixby_token' => env('LAUNDRY_BIXBY_TOKEN'),
    // Enable the Samsung hint only after the capsule is published and tested.
    'bixby_published' => (bool) env('LAUNDRY_BIXBY_PUBLISHED', false),
    'bixby_name' => env('LAUNDRY_BIXBY_NAME', 'Waku Waku Laundryhub'),
];
