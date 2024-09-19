<?php
class Router
{
    //private $start;  для отслеживания времени работы
    private $routes = [];
    private $pattern_list = [
        'id' => '[0-9]+',
        'slug' => '[a-z0-9-]+',
        'any' => '.*?',
        'base' => '[a-zA-Z0-9-_]+',
    ];

    public function __construct()
    {
        // $this->start = microtime(true); для отслеживания времени работы
    }

    public function addRoute(string $pattern, string $handler): void
    {
        $handler = explode('@', $handler);
        $this->routes[$pattern] = $handler;
    }

    private function url_api($name_var = null)
    {
        if ($name_var) {
            $name_var = explode(',', $name_var);
        }
        $url = explode('?', $_SERVER['REQUEST_URI'])[0];
        $parts = explode('/', $url);
        $index = $parts; // [0]=>'',[1]=>'Название страницы',[2...] элементы валидации-$var1...
        if ($index !== false) {
            if ($index[1] == 'api') { // если [0]=>'', [1]=>api [2]... то удаляем api и перезаписываем в $parts [1]=>Название страницы
                unset($index[1]);
                $parts = $index;
            }
            $values = array_slice($parts, 2); //Берет валидацию после страницы- Info/(Test)/((Ore)/(([A-Za-z0-9]+)|)|) будет 3 валидации
            $vars = [];
            foreach ($values as $key => $value) {
                $varName = isset($name_var[$key]) ? $name_var[$key] : 'var' . ($key + 1);
                $vars[$varName] = $value;
            }

            return $vars;
        }
    }
    /**
     * Вычиление паттерна для роутинга
     * @param string $pattern
     * @return array 
     * @throws Error
     * @example | `[/{name_var}]` | `[/{name_var:валидация}]` | `(/([валидация])|) без кастомного названия переменной`
     * @example | `[?{name_var}]` | `[?{name_var:валидация}]` | `([валидация]) без кастомного названия переменной`
     * @description  обрабатывает паттерны url адресов для роутинга
     */
    private function pattern($pattern)
    {
        try {
            $pattern = str_replace(['[/', '[?'], ['(/', '('], $pattern);
            $pattern = preg_replace_callback('/(\])+$/', function ($match) {
                return str_repeat('|)', strlen($match[0]));
            }, $pattern);
            $name_var = '';

            $pattern = preg_replace_callback('/\{\s*(.*?)\s*\}/', function ($match) use (&$name_var) {
                if (strpos($match[1], ':') !== false) {
                    $arr = explode(':', $match[1]);
                    $name_var .= "$arr[0],";
                    if ($arr[1] && !preg_match('/^\[/', $arr[1])) {
                        $arr[1] = $this->pattern_list[$arr[1]];
                    }
                } else {
                    $name_var .= "$match[1],";
                    $arr[1] = $this->pattern_list['base'];
                }
                return "($arr[1])";;
            }, $pattern);
            return ["$pattern", $name_var];
        } catch (Error $e) {
            echo $e->getMessage() . "<br>";
        }
    }

    private function handlerResult($result = [
        'error' => false,
        'error_code' => 404,
        'message' => '',
    ], callable|null $calback = null): void
    {
        if ($result['error']) {
            http_response_code($result['error_code']);
            echo $result['message'];
            exit;
        } else {
            if ($calback) {
                $calback();
            }
        }
    }

    public function handleRequest(string $url): void
    {
        try {
            if (substr($url, 0, 1) == '/') {
                $url = substr($url, 1);
            }
            foreach ($this->routes as $pattern => $handler) {
                [$pattern, $name_var] = $this->pattern($pattern);

                $pattern = "#^$pattern$#";
                if (preg_match($pattern, $url)) {

                    if (is_array($handler)) {
                        if (is_string($handler)) {
                            $handler = explode('@', $handler);
                        }
                        list($class, $method) = $handler;
                        // Проверяем, существует ли метод в классе
                        if (class_exists($class) && method_exists($class, $method)) {
                            $this->handlerResult(calback: function () use ($class, $method, $name_var) {
                                $controllerInstance = new $class();
                                $vars = $this->url_api($name_var);
                                call_user_func_array([$controllerInstance, $method], $vars); // Передаем все переменные из массива
                            });
                            return;
                        } elseif (!class_exists($class)) {
                            $this->handlerResult([
                                'error' => true,
                                'error_code' => 404,
                                'message' => "Класса {$class} не существует"
                            ]);
                            return;
                        } elseif (!method_exists($class, $method)) {
                            $this->handlerResult([
                                'error' => true,
                                'error_code' => 404,
                                'message' => "Метода {$method} в классе {$class} не существует"
                            ]);
                            return;
                        }
                    }
                }
            }
        } catch (Error $e) {
            echo "Error: " . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine();
            exit;
        }
        $this->handleNotFound();
    }

    private function handleNotFound(): void
    {

        http_response_code(404);
        echo "<html>
                    <body style='background-color: white;height: 100%;margin: 0;display: flex;align-items: center;justify-content: center;'>
                        <section class='base' id='what?' style=' text-align: center;border: solid #123213 1px;margin-top: 15em;'>
                        <p>Как вы сюда попали ?</p><p>В любом случае, 404 Not Found</p>
                        </section>
                    </body>
                </html>";
    }
}
