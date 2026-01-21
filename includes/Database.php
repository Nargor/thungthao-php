<?php
namespace App\includes;
use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../config.php';

        try {
            $driver = $this->config['db']['driver'] ?? 'mysql';
            if ($driver === 'none') {
                $this->pdo = null;
                return;
            }

            if ($driver === 'sqlite') {
                $path = $this->config['db']['sqlite_path'] ?? (__DIR__ . '/../storage/database.sqlite');
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                $dsn = "sqlite:" . $path;
                $this->pdo = new PDO(
                    $dsn,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                return;
            }

            if ($driver === 'sqlsrv') {
                $host = $this->config['db']['host'] ?? '';
                $port = $this->config['db']['port'] ?? '';
                $dbname = $this->config['db']['database'] ?? '';
                $server = $host . ($port ? "," . $port : "");
                $dsn = "sqlsrv:Server=" . $server . ";Database=" . $dbname;

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $charset = $this->config['db']['charset'] ?? null;
                if ($charset) {
                    if (defined('PDO::SQLSRV_ATTR_ENCODING') && defined('PDO::SQLSRV_ENCODING_UTF8')) {
                        $lower = strtolower($charset);
                        if ($lower === 'utf8' || $lower === 'utf-8') {
                            $options[PDO::SQLSRV_ATTR_ENCODING] = PDO::SQLSRV_ENCODING_UTF8;
                        }
                    }
                }

                $this->pdo = new PDO(
                    $dsn,
                    $this->config['db']['username'] ?? null,
                    $this->config['db']['password'] ?? null,
                    $options
                );
                return;
            }
        
        
            $dbname = $this->config['db']['database'] ?? '';
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['db']['host'],
                $this->config['db']['port'],
                $dbname,
                $this->config['db']['charset']
            );
            $this->pdo = new PDO(
                $dsn,
                $this->config['db']['username'],
                $this->config['db']['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo; 
    }
}
