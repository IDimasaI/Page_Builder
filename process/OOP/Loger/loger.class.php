<?php
class JSLoger
{
    public function __construct() {}
    /**
     * console_log
     * @param array $arg
     * @return script `console.log`
     * @description Выводит данные массива в консоль через json_encode либо строку
     */
    static public function console_log(array|string $arg)
    {
        $arg = json_encode($arg, JSON_UNESCAPED_UNICODE);
  
        $script = "<script>console.log($arg);</script>";
        return $script;
    }

    static public function console_error(array|string $arg)
    {
        $arg = json_encode($arg, JSON_UNESCAPED_UNICODE);
  
        $script = "<script>console.error($arg);</script>";
        return $script;
    }
}
