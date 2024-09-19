<?php

/**
 * Подключение по PDO к файлу sqlite
 */
class Database_lite
{
    private static $connection;
    /**
     *@param string $file путь к файлу.
     *@return self::$connection
     */
    public static function getConnection(string $file)
    {
        if (self::$connection === null) {
            try {
                // Изменяем DSN для подключения к SQLite
                self::$connection = new PDO("sqlite:$file");
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                self::failDB();
            }
        }
        return self::$connection;
    }
    
    public static function closeConnection()
    {
        self::$connection = null;
    }

    private static function failDB()
    {
        header("HTTP/1.1 501 Internal Server Error");
        echo json_encode(['status' => 'error', 'message' => 'Ошибка, БД не найдена']);
        exit;
    }
}
/**
 * Подключение к БД MySQL, не забудьте настроить конфиг в config/configDB.php , относительно файла с этим классом.
 *
 * @return self::$connection
 */
class Database //Подключение к БД
{
    private static $connection;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $config = require(__DIR__ . "/../config/configDB.php");

            try {
                // Указываем кодировку в DSN
                $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=' . $config['charset'];
                self::$connection = new PDO($dsn, $config['user'], $config['password']);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                self::failDB();
            }
        }


        return self::$connection;
    }
    public static function closeConnection()
    {
        self::$connection = null;
    }
    private static function failDB()
    {
        header("HTTP/1.1 501 Internal Server Error");
        echo json_encode(['status' => 'error', 'message' => 'Ошибка, БД не найдена']);
        exit;
    }
}
