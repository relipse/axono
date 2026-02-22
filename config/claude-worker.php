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
];
