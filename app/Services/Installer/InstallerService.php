<?php

namespace App\Services\Installer;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use PDO;
use Throwable;

class InstallerService
{
    /**
     * Determine if the application has already been installed.
     */
    public function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }

    /**
     * Acquire an exclusive, non-blocking install lock so the install check and
     * the work that follows it are atomic across concurrent requests.
     *
     * @return resource|null
     */
    public function acquireInstallLock()
    {
        $handle = fopen(storage_path('installing.lock'), 'c');

        if ($handle === false) {
            return null;
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return null;
        }

        return $handle;
    }

    /**
     * Release the exclusive install lock.
     *
     * @param  resource  $handle
     */
    public function releaseInstallLock($handle): void
    {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * Check server environment requirements and directory permissions.
     *
     * @return array{
     *     satisfied: bool,
     *     php_version: array{required: string, current: string, satisfied: bool},
     *     extensions: array<string, bool>,
     *     database_drivers: array<string, bool>,
     *     directories: array<string, bool>
     * }
     */
    public function checkRequirements(): array
    {
        $minPhpVersion = '8.2.0';
        $currentPhpVersion = PHP_VERSION;
        $phpSatisfied = version_compare($currentPhpVersion, $minPhpVersion, '>=');

        $requiredExtensions = [
            'pdo',
            'openssl',
            'mbstring',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'curl',
            'fileinfo',
        ];

        $extensionResults = [];
        $extensionsSatisfied = true;
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $extensionResults[$ext] = $loaded;
            if (! $loaded) {
                $extensionsSatisfied = false;
            }
        }

        $dbDrivers = [
            'sqlite' => extension_loaded('pdo_sqlite'),
            'mysql' => extension_loaded('pdo_mysql'),
        ];

        // At least one database driver (sqlite or mysql) must be available
        $dbSatisfied = $dbDrivers['sqlite'] || $dbDrivers['mysql'];

        $writablePaths = [
            'storage' => storage_path(),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $directoryResults = [];
        $directoriesSatisfied = true;
        foreach ($writablePaths as $name => $path) {
            $isWritable = is_writable($path);
            $directoryResults[$name] = $isWritable;
            if (! $isWritable) {
                $directoriesSatisfied = false;
            }
        }

        $allSatisfied = $phpSatisfied && $extensionsSatisfied && $dbSatisfied && $directoriesSatisfied;

        return [
            'satisfied' => $allSatisfied,
            'php_version' => [
                'required' => $minPhpVersion,
                'current' => $currentPhpVersion,
                'satisfied' => $phpSatisfied,
            ],
            'extensions' => $extensionResults,
            'database_drivers' => $dbDrivers,
            'directories' => $directoryResults,
        ];
    }

    /**
     * Test database connectivity for SQLite or MySQL before running migrations.
     *
     * @param array{
     *     driver: string,
     *     database_path?: string,
     *     host?: string,
     *     port?: int|string,
     *     database?: string,
     *     username?: string,
     *     password?: string|null
     * } $config
     * @return array{success: bool, message?: string, error?: string}
     */
    public function testDatabase(array $config): array
    {
        $driver = strtolower(trim($config['driver'] ?? 'sqlite'));

        if (! in_array($driver, ['sqlite', 'mysql'], true)) {
            return [
                'success' => false,
                'error' => "Unsupported database driver [{$driver}]. Please choose SQLite or MySQL.",
            ];
        }

        if ($driver === 'sqlite') {
            if (! extension_loaded('pdo_sqlite')) {
                return [
                    'success' => false,
                    'error' => 'PHP pdo_sqlite extension is not loaded on this server.',
                ];
            }

            $path = $this->sanitizeSqlitePath($config['database_path'] ?? null);
            $dir = dirname($path);

            if (! File::isDirectory($dir) && ! File::makeDirectory($dir, 0755, true, true)) {
                return [
                    'success' => false,
                    'error' => "Cannot create SQLite database directory at [{$dir}]. Check permissions.",
                ];
            }

            try {
                if (! File::exists($path)) {
                    File::put($path, '');
                }

                $pdo = new PDO("sqlite:{$path}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $pdo->query('SELECT 1');

                return [
                    'success' => true,
                    'message' => 'SQLite connection verified successfully. File: ['.basename($path).'].',
                ];
            } catch (Throwable $e) {
                return [
                    'success' => false,
                    'error' => 'SQLite connection error: '.$e->getMessage(),
                ];
            }
        }

        // MySQL Driver
        if (! extension_loaded('pdo_mysql')) {
            return [
                'success' => false,
                'error' => 'PHP pdo_mysql extension is not loaded on this server.',
            ];
        }

        $host = trim($config['host'] ?? '127.0.0.1');
        $port = (int) ($config['port'] ?? 3306);
        $database = trim($config['database'] ?? 'amadeus_gds');
        $username = trim($config['username'] ?? 'root');
        $password = (string) ($config['password'] ?? '');

        if (empty($database) || ! preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            return ['success' => false, 'error' => 'Database name must be alphanumeric and may only contain underscores.'];
        }

        // SSRF protection: block link-local metadata endpoints in any literal form
        // (IPv4, IPv4-mapped IPv6, or a hostname resolving to them).
        $hostToCheck = trim($host, ' []');
        $ip = filter_var($hostToCheck, FILTER_VALIDATE_IP) ? $hostToCheck : gethostbyname($hostToCheck);
        $packed = @inet_pton((string) $ip);
        if ($packed !== false) {
            // Unwrap IPv4-mapped IPv6 (::ffff:169.254.x.x, incl. ::ffff:a9fe:a9fe).
            if (strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10)."\xff\xff")) {
                $packed = substr($packed, 12);
            }
            // 169.254.0.0/16 link-local range.
            if (strlen($packed) === 4 && str_starts_with($packed, "\xa9\xfe")) {
                return ['success' => false, 'error' => 'Connection to link-local metadata addresses is forbidden.'];
            }
        }

        try {
            // First connect without specifying database to check server access
            $dsnNoDb = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsnNoDb, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 4,
            ]);

            // Ensure database exists or create it
            $safeDbName = str_replace('`', '``', $database);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Connect to specific database
            $pdo->exec("USE `{$safeDbName}`");
            $pdo->query('SELECT 1');

            return [
                'success' => true,
                'message' => "MySQL connection verified. Database [{$database}] is ready on {$host}:{$port}.",
            ];
        } catch (Throwable $e) {
            // Do not reflect driver/host:port detail: it turns this endpoint into a
            // reachability oracle for arbitrary internal addresses and services.
            return [
                'success' => false,
                'error' => 'Database connection failed. Verify the host, port and credentials.',
            ];
        }
    }

    /**
     * Configure runtime connection for selected database.
     *
     * @param array{
     *     driver: string,
     *     database_path?: string,
     *     host?: string,
     *     port?: int|string,
     *     database?: string,
     *     username?: string,
     *     password?: string|null
     * } $config
     */
    public function configureDatabase(array $config): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $driver = strtolower(trim($config['driver'] ?? 'sqlite'));

        if ($driver === 'sqlite') {
            $absolutePath = $this->sanitizeSqlitePath($config['database_path'] ?? null);
            File::ensureDirectoryExists(dirname($absolutePath));
            if (! File::exists($absolutePath)) {
                File::put($absolutePath, '');
            }

            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $absolutePath,
            ]);

            DB::purge('sqlite');
            DB::reconnect('sqlite');
            DB::setDefaultConnection('sqlite');

            return;
        }

        // MySQL
        $host = trim($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? 3306);
        $database = trim($config['database'] ?? 'amadeus_gds');
        $username = trim($config['username'] ?? 'root');
        $password = (string) ($config['password'] ?? '');

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
        DB::setDefaultConnection('mysql');
    }

    /**
     * Persist configured database parameters into .env file.
     *
     * @param array{
     *     driver: string,
     *     database_path?: string,
     *     host?: string,
     *     port?: int|string,
     *     database?: string,
     *     username?: string,
     *     password?: string|null
     * } $config
     */
    public function updateDatabaseEnv(array $config): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $driver = strtolower(trim($config['driver'] ?? 'sqlite'));

        if ($driver === 'sqlite') {
            $sqlitePath = $this->sanitizeSqlitePath($config['database_path'] ?? null);

            $this->updateEnv([
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => 'database/'.basename($sqlitePath),
            ]);

            return;
        }

        $this->updateEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => trim($config['host'] ?? '127.0.0.1'),
            'DB_PORT' => (string) ($config['port'] ?? 3306),
            'DB_DATABASE' => trim($config['database'] ?? 'amadeus_gds'),
            'DB_USERNAME' => trim($config['username'] ?? 'root'),
            'DB_PASSWORD' => (string) ($config['password'] ?? ''),
        ]);
    }

    /**
     * Persist configured application URL / domain into .env file and runtime config.
     */
    public function updateAppUrl(?string $appUrl): void
    {
        $url = trim($appUrl ?? '');
        if (empty($url)) {
            return;
        }

        $cleaned = rtrim($url, '/');
        config(['app.url' => $cleaned]);

        if (app()->environment('testing')) {
            return;
        }

        $this->updateEnv(['APP_URL' => $cleaned]);
    }

    /**
     * Atomically update or insert key-value pairs in the root .env file.
     *
     * @param  array<string, string|null>  $keyValues
     */
    public function updateEnv(array $keyValues): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            $examplePath = base_path('.env.example');
            if (File::exists($examplePath)) {
                File::copy($examplePath, $envPath);
            } else {
                File::put($envPath, '');
            }
        }

        $envContent = File::get($envPath);
        $newContent = $envContent;

        foreach ($keyValues as $key => $value) {
            $escapedValue = $this->formatEnvValue($value);
            $pattern = "/^{$key}=(.*)$/m";

            if (preg_match($pattern, $newContent)) {
                $newContent = preg_replace($pattern, "{$key}={$escapedValue}", $newContent);
            } else {
                $newContent .= "\n{$key}={$escapedValue}";
            }
        }

        if (trim($newContent) !== trim($envContent)) {
            File::put($envPath, $newContent);
        }
    }

    /**
     * Format a value for safe storage in .env.
     */
    protected function formatEnvValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (preg_match('/\s|"|\'|#|\$/', $value)) {
            return '"'.addcslashes($value, '"\\$').'"';
        }

        return $value;
    }

    /**
     * Run database migrations and seed reference data.
     */
    public function runMigrationsAndSeeders(): void
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $driver = config('database.default', 'sqlite');

        Artisan::call('migrate', [
            '--database' => $driver,
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--database' => $driver,
            '--force' => true,
        ]);
    }

    /**
     * Create the primary flight instructor account.
     *
     * @param array{
     *     name: string,
     *     username: string,
     *     email: string,
     *     password: string,
     *     office_id?: string,
     *     agent_id?: string
     * } $data
     */
    public function createInstructor(array $data): User
    {
        // Never modify an existing account during installation: a re-opened
        // installer must not be able to take over a provisioned account.
        $existing = User::where('username', $data['username'])
            ->orWhere('email', $data['email'])
            ->first();

        if ($existing) {
            throw new \RuntimeException(
                'An account with this username or email already exists. Installation cannot modify existing accounts.'
            );
        }

        return User::create([
            'name' => $data['name'] ?? 'Flight Instructor',
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'instructor',
            'is_approved' => true,
            'office_id' => $data['office_id'] ?? 'LON1A0980',
            'agent_id' => $data['agent_id'] ?? 'IN01GS',
        ]);
    }

    /**
     * Mark the application as installed by writing the lock file.
     */
    public function markInstalled(User $instructor, ?string $dbDriver = null): void
    {
        $driver = $dbDriver ?: config('database.default', 'sqlite');

        $payload = [
            'installed_at' => now()->toIso8601String(),
            'app_version' => '1.0.0',
            'app_url' => config('app.url'),
            'database_driver' => $driver,
            'installer_locked' => true,
            'installed_by' => [
                'id' => $instructor->id,
                'username' => $instructor->username,
                'email' => $instructor->email,
                'role' => 'instructor',
                'office_id' => $instructor->office_id,
                'agent_id' => $instructor->agent_id,
            ],
        ];

        File::put(storage_path('installed'), json_encode($payload, JSON_PRETTY_PRINT));
    }

    /**
     * Determine if a given filesystem path is absolute.
     */
    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':');
    }

    /**
     * Sanitize SQLite path to strictly prevent directory traversal and confine inside database/.
     */
    public function sanitizeSqlitePath(?string $rawPath): string
    {
        $input = trim($rawPath ?? 'database/database.sqlite');
        if (empty($input)) {
            $input = 'database/database.sqlite';
        }

        $cleanFilename = basename($input);
        if (! str_ends_with(strtolower($cleanFilename), '.sqlite')) {
            $cleanFilename .= '.sqlite';
        }

        return database_path($cleanFilename);
    }

    /**
     * Remove the installation lock file (used for testing or resetting).
     */
    public function uninstall(): void
    {
        if ($this->isInstalled()) {
            File::delete(storage_path('installed'));
        }
    }
}
