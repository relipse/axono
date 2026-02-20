<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SetupController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $checks = $this->runChecks();

        return view('setup.index', compact('checks'));
    }

    public function run(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $request->validate([
            'db_connection' => 'required|in:sqlite,mysql,pgsql',
            'db_host' => 'required_unless:db_connection,sqlite',
            'db_port' => 'required_unless:db_connection,sqlite|nullable|numeric',
            'db_database' => 'required_unless:db_connection,sqlite',
            'db_username' => 'required_unless:db_connection,sqlite',
            'db_password' => 'nullable|string',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
            'app_url' => 'required|url',
        ]);

        $driver = $request->input('db_connection');
        $steps = [];

        // Step 1: Write .env file
        try {
            $this->writeEnvFile($request);
            $steps[] = ['label' => 'Environment file created', 'ok' => true];
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['setup' => 'Failed to write .env file: ' . $e->getMessage()]);
        }

        // Reload config from the new .env
        Artisan::call('config:clear');

        // Step 2: For SQLite, create the database file
        if ($driver === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            if (!file_exists($dbPath)) {
                touch($dbPath);
            }
            $steps[] = ['label' => 'SQLite database file created', 'ok' => true];
        }

        // Step 3: Reconfigure the database connection at runtime
        try {
            $this->setDatabaseConfig($request);
            DB::purge();
            DB::reconnect();
            DB::connection()->getPdo();
            $steps[] = ['label' => 'Database connection verified', 'ok' => true];
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['setup' => 'Database connection failed: ' . $e->getMessage()]);
        }

        // Step 4: Generate app key
        try {
            Artisan::call('key:generate', ['--force' => true]);
            $steps[] = ['label' => 'Application key generated', 'ok' => true];
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['setup' => 'Key generation failed: ' . $e->getMessage()]);
        }

        // Step 5: Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            $steps[] = ['label' => 'Database tables created', 'ok' => true];
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['setup' => 'Migration failed: ' . $e->getMessage()]);
        }

        // Step 6: Seed subscription plans
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\SubscriptionPlanSeeder',
                '--force' => true,
            ]);
            $steps[] = ['label' => 'Subscription plans seeded', 'ok' => true];
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['setup' => 'Seeding failed: ' . $e->getMessage()]);
        }

        // Step 7: Create admin user
        try {
            $user = User::create([
                'name' => $request->input('admin_name'),
                'email' => $request->input('admin_email'),
                'password' => Hash::make($request->input('admin_password')),
                'email_verified_at' => now(),
            ]);
            $steps[] = ['label' => 'Admin account created', 'ok' => true];
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['setup' => 'Admin account creation failed: ' . $e->getMessage()]);
        }

        // Step 8: Write installed marker
        file_put_contents(storage_path('installed'), date('Y-m-d H:i:s'));

        // Log the admin in
        auth()->login($user);

        return redirect('/dashboard')->with('success', 'PostFlow has been installed successfully! Welcome aboard.');
    }

    private function isInstalled(): bool
    {
        if (!file_exists(base_path('.env'))) {
            return false;
        }

        if (!file_exists(storage_path('installed'))) {
            return false;
        }

        return true;
    }

    private function runChecks(): array
    {
        return [
            [
                'label' => 'PHP 8.2+',
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'value' => PHP_VERSION,
            ],
            [
                'label' => 'PDO extension',
                'ok' => extension_loaded('pdo'),
                'value' => extension_loaded('pdo') ? 'Loaded' : 'Missing',
            ],
            [
                'label' => 'PDO SQLite',
                'ok' => extension_loaded('pdo_sqlite'),
                'value' => extension_loaded('pdo_sqlite') ? 'Available' : 'Not available',
            ],
            [
                'label' => 'PDO MySQL',
                'ok' => extension_loaded('pdo_mysql'),
                'value' => extension_loaded('pdo_mysql') ? 'Available' : 'Not available',
            ],
            [
                'label' => 'Mbstring extension',
                'ok' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'Loaded' : 'Missing',
            ],
            [
                'label' => 'OpenSSL extension',
                'ok' => extension_loaded('openssl'),
                'value' => extension_loaded('openssl') ? 'Loaded' : 'Missing',
            ],
            [
                'label' => 'Storage directory writable',
                'ok' => is_writable(storage_path()),
                'value' => is_writable(storage_path()) ? 'Writable' : 'Not writable',
            ],
            [
                'label' => 'Bootstrap cache writable',
                'ok' => is_writable(base_path('bootstrap/cache')),
                'value' => is_writable(base_path('bootstrap/cache')) ? 'Writable' : 'Not writable',
            ],
        ];
    }

    private function writeEnvFile(Request $request): void
    {
        $driver = $request->input('db_connection');

        $env = file_get_contents(base_path('.env.example'));

        $replacements = [
            'APP_ENV=local' => 'APP_ENV=production',
            'APP_DEBUG=true' => 'APP_DEBUG=false',
            'APP_URL=http://localhost' => 'APP_URL=' . rtrim($request->input('app_url'), '/'),
            'DB_CONNECTION=mysql' => 'DB_CONNECTION=' . $driver,
        ];

        if ($driver === 'sqlite') {
            $replacements['DB_HOST=127.0.0.1'] = '# DB_HOST=127.0.0.1';
            $replacements['DB_PORT=3306'] = '# DB_PORT=3306';
            $replacements['DB_DATABASE=social_scheduler'] = 'DB_DATABASE=' . database_path('database.sqlite');
            $replacements['DB_USERNAME=root'] = '# DB_USERNAME=root';
            $replacements['DB_PASSWORD='] = '# DB_PASSWORD=';
        } else {
            $port = $driver === 'pgsql' ? ($request->input('db_port') ?: '5432') : ($request->input('db_port') ?: '3306');
            $replacements['DB_HOST=127.0.0.1'] = 'DB_HOST=' . $request->input('db_host');
            $replacements['DB_PORT=3306'] = 'DB_PORT=' . $port;
            $replacements['DB_DATABASE=social_scheduler'] = 'DB_DATABASE=' . $request->input('db_database');
            $replacements['DB_USERNAME=root'] = 'DB_USERNAME=' . $request->input('db_username');
            $replacements['DB_PASSWORD='] = 'DB_PASSWORD=' . ($request->input('db_password') ?? '');
        }

        $env = str_replace(array_keys($replacements), array_values($replacements), $env);

        file_put_contents(base_path('.env'), $env);
    }

    private function setDatabaseConfig(Request $request): void
    {
        $driver = $request->input('db_connection');

        config(["database.default" => $driver]);

        if ($driver === 'sqlite') {
            config(["database.connections.sqlite.database" => database_path('database.sqlite')]);
        } else {
            $port = $driver === 'pgsql' ? ($request->input('db_port') ?: '5432') : ($request->input('db_port') ?: '3306');
            config([
                "database.connections.{$driver}.host" => $request->input('db_host'),
                "database.connections.{$driver}.port" => $port,
                "database.connections.{$driver}.database" => $request->input('db_database'),
                "database.connections.{$driver}.username" => $request->input('db_username'),
                "database.connections.{$driver}.password" => $request->input('db_password') ?? '',
            ]);
        }
    }
}
