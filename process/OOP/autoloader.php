<?php
class CustomAutoloader
{
    private $classPaths = [
        'Database' => '/DB/DataBase.php',
        'Database_lite' => '/DB/DataBase.php',
        'ORM_lite' => '/DB/ORM_lite.class.php',
        'Router' => '/Router/Router.class.php',
        'JsonAPI' => '/JsonAPI/JsonAPI.class.php',
        'DmSession' => '/DmSession/DmSession.class.php',
        'DmSession_sql' => '/DmSession/DmSession_sql.class.php',
        'JsonCacher' => '/JsonCacher/JsonCacher.class.php',
        'POST_only' => '/secure/POST_only.class.php',
        'Template' => '/Template/Template.class.php',
        'Translater' => '/Translate/Translater.class.php',
        'JSLoger' => '/Loger/loger.class.php',
        'Profiler' => '/Profiler/profiler.class.php'
    ];


    private $loadedClasses = [];
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass($class)
    {
        if (array_key_exists($class, $this->loadedClasses)) {
            include $this->loadedClasses[$class];
        } elseif (array_key_exists($class, $this->classPaths) && !class_exists($class, false)) {
            include __DIR__ . $this->classPaths[$class];
            $this->loadedClasses[$class] = __DIR__ . $this->classPaths[$class];
        }
    }
}

$autoloader = new CustomAutoloader();
$autoloader->register();
