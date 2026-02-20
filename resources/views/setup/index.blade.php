<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PostFlow — Setup</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .setup-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, var(--pf-primary-50) 0%, var(--pf-gray-50) 50%, #fdf2f8 100%);
        }
        .setup-container {
            width: 100%;
            max-width: 640px;
        }
        .setup-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-brand svg {
            width: 48px;
            height: 48px;
            color: var(--pf-primary-600);
            margin-bottom: 0.75rem;
        }
        .setup-brand h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--pf-primary-600), var(--pf-accent-500));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .setup-brand p {
            color: var(--pf-gray-500);
            margin-top: 0.25rem;
        }

        .setup-card {
            background: #fff;
            border-radius: var(--pf-radius-xl);
            box-shadow: var(--pf-shadow-lg);
            overflow: hidden;
        }
        .setup-section {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--pf-gray-100);
        }
        .setup-section:last-child {
            border-bottom: none;
        }
        .setup-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--pf-gray-900);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .setup-section-title .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--pf-primary-600);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .setup-section-desc {
            color: var(--pf-gray-500);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            padding-left: 2rem;
        }

        .setup-checks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            padding-left: 2rem;
        }
        .check-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--pf-gray-700);
        }
        .check-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .check-icon.pass { color: var(--pf-success-500); }
        .check-icon.fail { color: var(--pf-danger-500); }

        .db-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        .db-tab {
            padding: 0.5rem 1rem;
            border-radius: var(--pf-radius-md);
            border: 2px solid var(--pf-gray-200);
            background: #fff;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--pf-gray-600);
            transition: all 0.15s;
        }
        .db-tab:hover {
            border-color: var(--pf-primary-300);
        }
        .db-tab.active {
            border-color: var(--pf-primary-600);
            background: var(--pf-primary-50);
            color: var(--pf-primary-700);
        }

        .db-fields {
            display: none;
            padding-left: 2rem;
        }
        .db-fields.active {
            display: block;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .field-row.full {
            grid-template-columns: 1fr;
        }

        .setup-footer {
            padding: 1.5rem 2rem;
            background: var(--pf-gray-50);
            border-top: 1px solid var(--pf-gray-100);
        }

        .setup-submit {
            width: 100%;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: var(--pf-radius-lg);
            background: linear-gradient(135deg, var(--pf-primary-600), var(--pf-primary-700));
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.01em;
        }
        .setup-submit:hover {
            background: linear-gradient(135deg, var(--pf-primary-700), var(--pf-primary-800));
            box-shadow: var(--pf-shadow-md);
        }
        .setup-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .sqlite-note {
            padding: 0.75rem 1rem;
            background: var(--pf-primary-50);
            border-radius: var(--pf-radius-md);
            font-size: 0.85rem;
            color: var(--pf-primary-700);
            padding-left: 2rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="setup-wrapper">
        <div class="setup-container">
            {{-- Brand --}}
            <div class="setup-brand">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="12" fill="currentColor"/>
                    <path d="M14 24L20 30L34 16" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1>PostFlow Setup</h1>
                <p>Let's get your instance up and running.</p>
            </div>

            {{-- Errors --}}
            @if($errors->any())
                <div class="pf-alert pf-alert-danger" style="margin-bottom: 1.5rem;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('setup.run') }}" id="setupForm">
                @csrf
                <div class="setup-card">

                    {{-- Section 1: System Checks --}}
                    <div class="setup-section">
                        <div class="setup-section-title">
                            <span class="step-num">1</span>
                            System Requirements
                        </div>
                        <div class="setup-section-desc">Checking your server meets the requirements.</div>
                        <div class="setup-checks">
                            @foreach($checks as $check)
                                <div class="check-item">
                                    @if($check['ok'])
                                        <svg class="check-icon pass" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @else
                                        <svg class="check-icon fail" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                    @endif
                                    {{ $check['label'] }} <span style="color: var(--pf-gray-400);">({{ $check['value'] }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Section 2: Application URL --}}
                    <div class="setup-section">
                        <div class="setup-section-title">
                            <span class="step-num">2</span>
                            Application URL
                        </div>
                        <div class="setup-section-desc">The public URL where PostFlow will be accessed.</div>
                        <div style="padding-left: 2rem;">
                            <div class="pf-form-group">
                                <input type="url" name="app_url" class="pf-input" placeholder="https://postflow.example.com" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Database --}}
                    <div class="setup-section">
                        <div class="setup-section-title">
                            <span class="step-num">3</span>
                            Database
                        </div>
                        <div class="setup-section-desc">Choose your database engine and enter credentials.</div>

                        <input type="hidden" name="db_connection" id="dbConnection" value="{{ old('db_connection', 'sqlite') }}">

                        <div class="db-tabs">
                            <button type="button" class="db-tab active" data-driver="sqlite">SQLite</button>
                            <button type="button" class="db-tab" data-driver="mysql">MySQL</button>
                            <button type="button" class="db-tab" data-driver="pgsql">PostgreSQL</button>
                        </div>

                        {{-- SQLite panel --}}
                        <div class="db-fields active" id="db-sqlite">
                            <div class="sqlite-note">
                                No configuration needed — a file-based database will be created automatically. Great for getting started quickly.
                            </div>
                        </div>

                        {{-- MySQL panel --}}
                        <div class="db-fields" id="db-mysql">
                            <div class="field-row" style="margin-bottom: 0.75rem;">
                                <div class="pf-form-group">
                                    <label class="pf-label">Host</label>
                                    <input type="text" name="db_host" class="pf-input" placeholder="127.0.0.1" value="{{ old('db_host', '127.0.0.1') }}">
                                </div>
                                <div class="pf-form-group">
                                    <label class="pf-label">Port</label>
                                    <input type="text" name="db_port" class="pf-input" placeholder="3306" value="{{ old('db_port', '3306') }}">
                                </div>
                            </div>
                            <div class="field-row full" style="margin-bottom: 0.75rem;">
                                <div class="pf-form-group">
                                    <label class="pf-label">Database name</label>
                                    <input type="text" name="db_database" class="pf-input" placeholder="postflow" value="{{ old('db_database', 'postflow') }}">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="pf-form-group">
                                    <label class="pf-label">Username</label>
                                    <input type="text" name="db_username" class="pf-input" placeholder="root" value="{{ old('db_username', 'root') }}">
                                </div>
                                <div class="pf-form-group">
                                    <label class="pf-label">Password</label>
                                    <input type="password" name="db_password" class="pf-input" placeholder="(blank for none)">
                                </div>
                            </div>
                        </div>

                        {{-- PostgreSQL panel --}}
                        <div class="db-fields" id="db-pgsql">
                            <div class="field-row" style="margin-bottom: 0.75rem;">
                                <div class="pf-form-group">
                                    <label class="pf-label">Host</label>
                                    <input type="text" name="db_host" class="pf-input" placeholder="127.0.0.1" value="{{ old('db_host', '127.0.0.1') }}">
                                </div>
                                <div class="pf-form-group">
                                    <label class="pf-label">Port</label>
                                    <input type="text" name="db_port" class="pf-input" placeholder="5432" value="{{ old('db_port', '5432') }}">
                                </div>
                            </div>
                            <div class="field-row full" style="margin-bottom: 0.75rem;">
                                <div class="pf-form-group">
                                    <label class="pf-label">Database name</label>
                                    <input type="text" name="db_database" class="pf-input" placeholder="postflow" value="{{ old('db_database', 'postflow') }}">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="pf-form-group">
                                    <label class="pf-label">Username</label>
                                    <input type="text" name="db_username" class="pf-input" placeholder="postgres" value="{{ old('db_username', 'postgres') }}">
                                </div>
                                <div class="pf-form-group">
                                    <label class="pf-label">Password</label>
                                    <input type="password" name="db_password" class="pf-input" placeholder="(blank for none)">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 4: Admin Account --}}
                    <div class="setup-section">
                        <div class="setup-section-title">
                            <span class="step-num">4</span>
                            Admin Account
                        </div>
                        <div class="setup-section-desc">Create your first user account.</div>
                        <div style="padding-left: 2rem;">
                            <div class="field-row full" style="margin-bottom: 0.75rem;">
                                <div class="pf-form-group">
                                    <label class="pf-label">Name</label>
                                    <input type="text" name="admin_name" class="pf-input" placeholder="Your name" value="{{ old('admin_name') }}" required>
                                </div>
                            </div>
                            <div class="field-row full" style="margin-bottom: 0.75rem;">
                                <div class="pf-form-group">
                                    <label class="pf-label">Email</label>
                                    <input type="email" name="admin_email" class="pf-input" placeholder="you@example.com" value="{{ old('admin_email') }}" required>
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="pf-form-group">
                                    <label class="pf-label">Password</label>
                                    <input type="password" name="admin_password" class="pf-input" placeholder="Min. 8 characters" required minlength="8">
                                </div>
                                <div class="pf-form-group">
                                    <label class="pf-label">Confirm password</label>
                                    <input type="password" name="admin_password_confirmation" class="pf-input" placeholder="Confirm password" required minlength="8">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="setup-footer">
                        <button type="submit" class="setup-submit" id="submitBtn">
                            Install PostFlow
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Database tab switching
        document.querySelectorAll('.db-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const driver = tab.dataset.driver;

                // Update active tab
                document.querySelectorAll('.db-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Update hidden field
                document.getElementById('dbConnection').value = driver;

                // Show matching fields panel
                document.querySelectorAll('.db-fields').forEach(f => f.classList.remove('active'));
                document.getElementById('db-' + driver).classList.add('active');
            });
        });

        // Restore tab selection from old input
        const saved = document.getElementById('dbConnection').value;
        if (saved) {
            const tab = document.querySelector('.db-tab[data-driver="' + saved + '"]');
            if (tab) tab.click();
        }

        // Show spinner on submit
        document.getElementById('setupForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Installing… please wait';
        });
    </script>
</body>
</html>
