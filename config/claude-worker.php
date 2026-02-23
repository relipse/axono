<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Claude Worker Admin Password
    |--------------------------------------------------------------------------
    |
    | Single password to protect the Claude Worker admin panel.
    | Set via the CLAUDE_WORKER_PASSWORD environment variable.
    |
    | Example: CLAUDE_WORKER_PASSWORD=my-secret-password
    |
    */
    'admin_password' => env('CLAUDE_WORKER_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key (for Whisper voice transcription)
    |--------------------------------------------------------------------------
    |
    | Used as a fallback when the browser's Web Speech API is unavailable.
    | Users can also provide their own key per-session in the voice page.
    |
    */
    'openai_api_key' => env('OPENAI_API_KEY', ''),
];
