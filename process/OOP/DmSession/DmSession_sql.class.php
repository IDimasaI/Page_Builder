<?php
class DmSession_sql
{
    private function create_file($config){
        $path = dirname(__DIR__, 2) . "/".dirname($config['path']);
        $name = basename($config['path']);
        $file = $path . "/{$name}";

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($file)) {
            $handle = fopen($file, 'w');
            if ($handle) {
                fclose($handle);
            } else {
                echo "Cannot create file: " . $file;
            }
        }
        return null;
    }
    /**
     * @var string $cookieLifetime время жизни куки
     */
    private $cookieLifetime;
    
    /**
     * @var string $name имя сессии
     */
    private $name;
    
    /**
     * @var array $config конфигруация БД и сессии
    */
    private $config;

    /**
     * @param string $name  имя сессии
     * @param int $time  время жизни сессии
     * 
     */
    public function __construct($name, $time = 3600 * 2)
    {
        $this->cookieLifetime = $time;
        $this->name = $name;
        $this->config = require(__DIR__ . "/../config/Config_DmSession_sql.php");
    }
    /**
     * Инициализация БД файла, если не уверены что она существует
    */
    public function init(){
        $this->create_file($this->config);
    }
    public function write_data()
    {  
        echo dirname(__DIR__, 2);
    }
    public function read_data()
    {
    }
    public function delete_old_data()
    {
    }
}
