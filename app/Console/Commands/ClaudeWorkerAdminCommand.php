<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClaudeWorkerAdminCommand extends Command
{
    protected $signature = 'claude-worker:password
                            {password? : The admin password to set (omit to show current status)}';

    protected $description = 'Set or check the Claude Worker admin password';

    public function handle(): int
    {
        $password = $this->argument('password');

        if (!$password) {
            return $this->showStatus();
        }

        return $this->setPassword($password);
    }

    protected function showStatus(): int
    {
        $current = config('claude-worker.admin_password');

        if (empty($current)) {
            $this->warn('No admin password configured.');
            $this->line('');
            $this->line('Set one in your .env file:');
            $this->line('  CLAUDE_WORKER_PASSWORD=your-secret-password');
            $this->line('');
            $this->line('Or run:');
            $this->line('  php artisan claude-worker:password your-secret-password');
        } else {
            $this->info('Admin password is configured.');
            $this->line('  Login at: ' . url('/claude-worker/login'));
        }

        return self::SUCCESS;
    }

    protected function setPassword(string $password): int
    {
        $envPath = base_path('.env');
        $contents = file_get_contents($envPath);

        if (preg_match('/^CLAUDE_WORKER_PASSWORD=.*/m', $contents)) {
            $contents = preg_replace(
                '/^CLAUDE_WORKER_PASSWORD=.*/m',
                'CLAUDE_WORKER_PASSWORD=' . $password,
                $contents
            );
        } else {
            $contents .= "\nCLAUDE_WORKER_PASSWORD=" . $password . "\n";
        }

        file_put_contents($envPath, $contents);

        $this->info('Admin password set successfully.');
        $this->line('  Login at: ' . url('/claude-worker/login'));

        return self::SUCCESS;
    }
}
