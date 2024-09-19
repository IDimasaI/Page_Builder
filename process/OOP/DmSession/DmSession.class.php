<?php
class DmSession
{
    private $name;
    private $cookieLifetime;
    private $SameSite;

    public function __construct($name, $SameSite = null, $time = 3600 * 2)
    {
        $this->name = $name;
        $this->cookieLifetime = $time;
        $this->SameSite = $SameSite;
    }

    public function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_name($this->name);
            if (!isset($_COOKIE[$this->name])) {
                $randomBytes = bin2hex(random_bytes(32));
                $bigRandom = $randomBytes . session_create_id();
                $_COOKIE[$this->name] = $bigRandom;
            }

            session_id($_COOKIE[$this->name]);
            session_start();
            $_COOKIE[$this->name] = session_id();

            if (!isset($_COOKIE[$this->name])) {
                session_regenerate_id(true);
                $this->setSessionCookie($this->cookieLifetime);
            } else {
                $this->setSessionCookie($this->cookieLifetime);
            }
        }
    }

    private function setSessionCookie($lifetime)
    {
        $config = require(__DIR__."/../config/Config_DmSession.php");
        $secure_auto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? true : false;
        $secure = ($config['secure'] === 'auto') ? $secure_auto : $config['secure'];

        $SameSite = ($this->SameSite) ? $this->SameSite : $config['SameSite'];
        $arr_cookie_options = array(
            'expires' => time() + $lifetime,
            'path' => $config['path'],
            'domain' => $config['domain'],
            'secure' => $secure,
            'httponly' => $config['httponly'],
            'SameSite' => $SameSite
        );
        setcookie(session_name(), session_id(), $arr_cookie_options);
    }

    public function stop()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }
}
?>