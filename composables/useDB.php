<?php

function useDB()
{
    return [
        'connect' => function () {
            try {
                $config = getConfig();

                show_error_log('DB Config: ' . print_r($config['db'], true));

                $dbConfig = $config['db'];
                $driver = $dbConfig['driver'] ?? 'mysql';

                if ($driver === 'none') {
                    show_error_log('DB driver is set to none. Skipping connection.');
                    return null;
                }

                if ($driver === 'sqlite') {
                    $path = $dbConfig['sqlite_path'] ?? (__DIR__ . '/../storage/database.sqlite');
                    $dsn = 'sqlite:' . $path;
                    show_error_log('Attempting SQLite connection with DSN: ' . $dsn);
                    $db = new PDO(
                        $dsn,
                        null,
                        null,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );
                    show_error_log('SQLite connection successful');
                    return $db;
                }

                if ($driver === 'sqlsrv') {
                    $requiredSettings = ['host', 'database', 'username', 'port', 'charset'];
                    foreach ($requiredSettings as $setting) {
                        if (!isset($dbConfig[$setting]) || $dbConfig[$setting] === null) {
                            show_error_log("Missing required database setting: $setting");
                            throw new Exception("Database configuration error: missing $setting");
                        }
                    }

                    $server = $dbConfig['host'] . ($dbConfig['port'] ? "," . $dbConfig['port'] : "");
                    $dsn = 'sqlsrv:Server=' . $server . ';Database=' . $dbConfig['database'];
                    show_error_log('Attempting SQLSRV connection with DSN: ' . $dsn);
                    $password = $dbConfig['password'] ?? '';

                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ];

                    if (defined('PDO::SQLSRV_ATTR_ENCODING') && defined('PDO::SQLSRV_ENCODING_UTF8')) {
                        $lower = strtolower($dbConfig['charset']);
                        if ($lower === 'utf8' || $lower === 'utf-8') {
                            $options[PDO::SQLSRV_ATTR_ENCODING] = PDO::SQLSRV_ENCODING_UTF8;
                        }
                    }

                    $db = new PDO(
                        $dsn,
                        $dbConfig['username'],
                        $password,
                        $options
                    );
                    show_error_log('SQLSRV connection successful');
                    return $db;
                }

                $requiredSettings = ['host', 'database', 'username', 'port', 'charset'];
                foreach ($requiredSettings as $setting) {
                    if (!isset($dbConfig[$setting]) || $dbConfig[$setting] === null) {
                        show_error_log("Missing required database setting: $setting");
                        throw new Exception("Database configuration error: missing $setting");
                    }
                }

                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['database'],
                    $dbConfig['charset']
                );

                show_error_log('Attempting to connect with DSN: ' . $dsn);
                show_error_log('Username: ' . $dbConfig['username']);
                $password = $dbConfig['password'] ?? '';

                $db = new PDO(
                    $dsn,
                    $dbConfig['username'],
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

                show_error_log('MySQL connection successful');
                return $db;

            } catch (PDOException $e) {
                show_error_log('PDO connection error: ' . $e->getMessage());
                show_error_log('PDO error code: ' . $e->getCode());
                throw new Exception('Database connection error: ' . $e->getMessage());
            } catch (Exception $e) {
                show_error_log('General error in database connection: ' . $e->getMessage());
                throw $e;
            }
        }
    ];
}

return useDB();
