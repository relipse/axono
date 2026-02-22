<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ClaudeWorkerAdminCommand extends Command
{
    protected $signature = 'claude-worker:admin
                            {action : grant, revoke, or list}
                            {email? : User email address (required for grant/revoke)}';

    protected $description = 'Manage Claude Worker admin access for user accounts';

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'grant' => $this->grantAccess(),
            'revoke' => $this->revokeAccess(),
            'list' => $this->listAdmins(),
            default => $this->invalidAction($action),
        };
    }

    protected function grantAccess(): int
    {
        $email = $this->argument('email');
        if (!$email) {
            $this->error('Email is required for grant action.');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $user->is_claude_worker_admin = true;
        $user->save();

        $this->info("Granted Claude Worker admin access to {$user->name} ({$email})");
        return self::SUCCESS;
    }

    protected function revokeAccess(): int
    {
        $email = $this->argument('email');
        if (!$email) {
            $this->error('Email is required for revoke action.');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $user->is_claude_worker_admin = false;
        $user->save();

        $this->info("Revoked Claude Worker admin access from {$user->name} ({$email})");
        return self::SUCCESS;
    }

    protected function listAdmins(): int
    {
        $dbAdmins = User::where('is_claude_worker_admin', true)->get(['name', 'email']);
        $envEmails = config('claude-worker.admin_emails', []);

        $this->info('Database admins:');
        if ($dbAdmins->isEmpty()) {
            $this->line('  (none)');
        } else {
            foreach ($dbAdmins as $user) {
                $this->line("  {$user->name} <{$user->email}>");
            }
        }

        $this->newLine();
        $this->info('Environment-configured admins (CLAUDE_WORKER_ADMIN_EMAILS):');
        if (empty($envEmails)) {
            $this->line('  (none)');
        } else {
            foreach ($envEmails as $email) {
                $this->line("  {$email}");
            }
        }

        return self::SUCCESS;
    }

    protected function invalidAction(string $action): int
    {
        $this->error("Unknown action: {$action}. Use grant, revoke, or list.");
        return self::FAILURE;
    }
}
