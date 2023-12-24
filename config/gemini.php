<?php

declare(strict_types=1);

return [
    /**
     * Gemini API Key
     *
     * You will need an API key to access the Gemini API.
     * You can obtain it from Google AI Studio ( https://makersuite.google.com/ )
     */
    'api_key' => env('GEMINI_API_KEY'),

    /**
     * Gemini Base URL
     *
     * If you need a specific base URL for the Gemini API, you can provide it here.
     * Otherwise, leave empty to use the default value.
     */
    'base_url' => env('GEMINI_BASE_URL'),
];
