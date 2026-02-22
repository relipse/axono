<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Claude Worker Admin Emails
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of email addresses that should have access to the
    | Claude Worker admin panel, in addition to users with the database flag.
    | Set via the CLAUDE_WORKER_ADMIN_EMAILS environment variable.
    |
    | Example: CLAUDE_WORKER_ADMIN_EMAILS="admin@example.com,dev@example.com"
    |
    */
    'admin_emails' => array_filter(
        array_map('trim', explode(',', env('CLAUDE_WORKER_ADMIN_EMAILS', '')))
    ),
];
