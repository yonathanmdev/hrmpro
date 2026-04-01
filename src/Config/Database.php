<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            // የኮንፊግ ፋይሉ ያለበትን መንገድ በትክክል ማግኘት
            // ፋይሉ በ src/Config/db_config.php ውስጥ ካለ፡
            $configPath = __DIR__ . '/db_config.php';

            if (!file_exists($configPath)) {
                die("ስህተት፡ የዳታቤዝ መረጃ ፋይሉ (db_config.php) አልተገኘም። መንገድ፦ " . $configPath);
            }

            $config = require $configPath;

            try {
                $dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['db_name'] . ";charset=" . $config['charset'];
                
                self::$conn = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch(PDOException $exception) {
                // ለደህንነት ሲባል ስህተቱን በ log ፋይል ላይ ብቻ መያዝ ይሻላል
                error_log("የዳታቤዝ ግንኙነት ስህተት፦ " . $exception->getMessage());
                die("የዳታቤዝ ግንኙነት አልተሳካም። እባክዎ ቆይተው ይሞክሩ።");
            }
        }
        return self::$conn;
    }
}