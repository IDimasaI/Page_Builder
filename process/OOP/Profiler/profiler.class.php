<?php
class Profiler
{
    private static $start_time = null;
    private static $start_memory = null;

    public function __construct() {}

    public static function start()
    {
        self::$start_time = microtime(true);
        self::$start_memory = memory_get_usage();
    }

    public static function stop()
    {

        if (self::$start_time === null || self::$start_memory === null) {
            throw new \LogicException('Profiler not started');
        }

        return [
            'Execution time' => round((microtime(true) - self::$start_time) * 1000, 2) . " ms",
            'Memory' => round((memory_get_usage() - self::$start_memory) / 1024 / 1024, 2) . " MB",
        ];
    }
    public static function getStatus()
    {
        return [
            'start_time' => self::$start_time,
            'start_memory' => self::$start_memory,
            'execution_time' => self::$start_time === null ? null : round((microtime(true) - self::$start_time) * 1000, 2) . " ms",
            'memory' => self::$start_memory === null ? null : round((memory_get_usage() - self::$start_memory) / 1024 / 1024, 2) . " MB",
        ];
    }

    public static function saveResultsToFile($fileName)
    {
        $results = self::getStatus();
        file_put_contents($fileName, json_encode($results,JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND);
    }
}
