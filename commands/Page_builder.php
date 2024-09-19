<?php
$filesJson = json_decode(file_get_contents(__DIR__ . '/../Pages.config.json', true), true);

$folder = 'Pages';

function create_file($folder)
{
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
        echo "Папка успешно создана.\n";
    } else {
        echo "Папка уже существует.\n";
    }
}

create_file($folder);

$controllerContent = file_get_contents(__DIR__ . '/../routers/controllers.php');
function malware($controller, $template_engine, $filesJson, $router, $vars_notnull)
{

    $func = $filesJson['controller-function'][$controller] ?? null;
    if ($template_engine) {
        $global_vars = null;
        if (isset($router['use_template']['vars->include'])) { // Global vars
            $data = $router['use_template']['vars->include'];
            $keyValuePairs_global_Var = [];
            $var_key_global_Var = [];
            $var_key_global_nonVar = [];
            $include_template_vars = [];
            foreach ($data as $key => $value) {
                if (strpos($key, 'var') === 0) {
                    $keyValuePairs_global_Var[] = "$$key=$value;";
                    $var_key_global_Var[] = "$$key";
                    $include_template_vars[] = "'$key'=>$value";
                } else {

                    $var_key_global_nonVar[] = "$$key";
                    $include_template_vars[] = "'$key'=>$value";
                }
            }

            $include_template_vars = implode(",", $include_template_vars);
        }
        if (isset($router['use_template']['template_vars'])) { // Template vars
            $vars  = $router['use_template']['template_vars'];
            $keyValuePairs = [];
            $use = null;
            if (isset($global_vars)) {
                $global_vars_use_var = implode(",", $var_key_global_Var);
                $global_vars_use_nonVar = implode(",", $var_key_global_nonVar);
                $use = "use({$global_vars_use_var},{$global_vars_use_nonVar})";
            }
            foreach ($vars as $key => $value) { //Перебор значений в ['use_template']['vars']
                if (strpos($value, '$this->') !== false) { //Если в строке есть слово func то заменять его на $this->
                    $value = str_replace('$this-> ', '', $value);
                    $value = '' . $value;
                } elseif (strpos($value, "function") !== false && strpos($value, "include") !== false) {
                    $pattern = '/function\((.*?)\)/';
                    preg_match($pattern, $value, $matches);
                    $pattern_include = '/include\((.*?)\)/';
                    preg_match($pattern_include, $value, $matches_include);
                    $value_vars = $matches[1] ?? '';
                    $include = $matches_include[1] ?? '';
                    $value = str_replace('function ', '', $value);
                    $value = str_replace("function($value_vars)", '', $value);
                    $value = "\$this->includeFile($include,[$include_template_vars])";
                } elseif (strpos($value, 'function') !== false) {
                    $value = str_replace('function ', '', $value);
                    $value = "function(){$use}{ob_start();$value;return ob_get_clean();}";
                } else { //Иначе выводить как строку
                    $value = "\"{$value}\"";
                }
                $keyValuePairs[] = "\t\t\t\"$key\"=>$value";
            }

            $result = implode(",\n", $keyValuePairs);
        } else {
            $result = null;
        }
        if (isset($router['use_template']['vars_render'])) { // Render vars
            $vars  = $router['use_template']['vars_render'];
            $keyValuePairs = [];
            foreach ($vars as $key => $value) {

                $keyValuePairs[] = "'$key'=>$value";
            }

            $vars_render = implode(',', $keyValuePairs);
        } else {
            $vars_render = null;
        }
        $result_not_null = $result !== null ? "\n$result" : null;
        $varRender = "";
        if (isset($vars_render)) {
            $varRender = "{$vars_render}";
        }
        if (isset($vars_notnull) && !empty($vars_notnull)) {
            $varsRenderTemp = ",{$vars_notnull}";
            if (isset($varRender) && $varRender !== ',') {
                $varRender .= "{$varsRenderTemp}";
            } else {
                $varRender = $varsRenderTemp;
            }
        }

        if (isset($vars_render) && isset($vars_notnull)) {
            $vars_render = "{$vars_render},{$vars_notnull}";
        }
        if (!isset($router['use_InnerScript']) && empty($router['use_InnerScript'])) {
            return "\$template=new Template(\"Pages/{$router['name_file']}\",[
            \"headAssets\" => \$this->assetsHead,$result_not_null
        ]);\n        \$page= \$template->render([$varRender]);\n        echo \$page;\n\t";
        } else {
            $props = json_encode($router['use_InnerScript']['props'], JSON_UNESCAPED_SLASHES) ?? null;
            $props = str_replace(["{", "}", ":"], ["[", "]", "=>"], $props);
            return "\$template=new Template(\"Pages/{$router['name_file']}\",[
            \"headAssets\" => \$this->assetsHead,$result_not_null
        ]);\n        \$page= \$template->InnerScriptRender([$varRender],$props);\n        echo \$page;\n\t";
        }
    }
    if (!$template_engine) {
        if (isset($router['is_API']) && $router['is_API'] == 'true') {
            return null;
        } else {
            return "$func\n        echo \$this->assetsHead;";
        }
    }
}

foreach ($filesJson['Pages'] as $router) {
    $fileName = $router['name_file'];

    $template_engine = (isset($router['use_template']['template']) && $router['use_template']['template'] == 'true') ? true : false;
    $react_use = (isset($router['use_react']) && $router['use_react'] == 'true') ? "'react'=>true," : null;
    $vue_use = (isset($router['use_vue']) && $router['use_vue'] == 'true') ? "'vue'=>true," : null;
    $HTMX_use = (isset($router['use_HTMX']['HTMX']) && $router['use_HTMX']['HTMX'] == 'true') ? "'HTMX'=>true," : null;
    $HTMX_preloader_use = (isset($router['use_HTMX']['HTMX_preloader']) && $router['use_HTMX']['HTMX_preloader'] == 'true') ? "'HTMX_preloader'=>true," : null;

    $is_api = (isset($router['is_API']) && $router['is_API'] == 'true') ? "'is_API'=>true" : null;
    $api_var = '';
    if ($is_api) {
        $folder = (isset($filesJson['API_folder'])) ? $filesJson['API_folder'] : 'process/API';
        create_file($folder);
    } else {
        $folder = "Pages";
    }

    $csp_nonce = (isset($router['csp_nonce']) && $router['csp_nonce'] == 'true') ? "'csp_nonce'=>true" : null;

    if ($filesJson['Page-template'] == 'base' && (!isset($router['pattern']) || empty($router['pattern']))) { // Шаблон содержимого в файле страницы  
        if ($is_api) {
            $fileContent = "";
        } else {
            $fileContent =
                "<head>
       <meta charset=\"utf-8\">
       <meta name=\"theme-color\" content=\"rgba(255, 255, 255, 0.98)\">
       <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
       <meta name=\"creator\" content=\"OpenCods\">
       <meta name=\"author\" content=\"Dimasa\">
       <title>Страница в разработке</title>
      </head>
     <body><p style=\"text-align: center;margin-top: 3em;\">Страницы еще не существует, вернитесь позже.</p></body>";
        }
    } elseif ($router['pattern']) {
        if (file_exists($router['pattern'])) {
            $fileContent = file_get_contents($router['pattern']);
        } else {
            $fileContent = $router['pattern'];
        }
    } else {
        if (file_exists($filesJson['Page-template'])) {
            $fileContent = file_get_contents($filesJson['Page-template']);
        } else {
            $fileContent = $filesJson['Page-template'];
        }
    }

    if (!file_exists("$folder/$fileName")) {
        file_put_contents("$folder/$fileName", $fileContent);
        echo "Файл $fileName успешно создан.\n";
    } else {
        echo "Файл $fileName уже есть.\n";
    }

    $functionName = $router['name_func'];
    $controllerType = $router['controller'];
    $nameController = $controllerType . "Controller";
    $functionExists = false;

    $validationUrl = $router['url'];
    $slashCount = substr_count($validationUrl, '/');



    $vars = "";
    $vars_notnull = "";
    $name_var_urls = [];
    if (preg_match_all('/\{\s*(.*?)\s*\}/', $router['url'], $matches)) {

        $name_var_urls = $matches[1];
        foreach ($name_var_urls as $var) {
            $vars .= "\$" . explode(':',$var)[0] . "=null,";
        }
        $vars = rtrim($vars, ",");

        foreach ($name_var_urls as $var) {
            $vars_notnull .= "'" . $var . "'=>\$" . $var . ",";
        }
        $vars_notnull = rtrim($vars_notnull, ",");
    } else {

        for ($i = 1; $i <= $slashCount; $i++) {
            $vars .= "\$var" . $i . "=null,";
        }
        $vars = rtrim($vars, ",");

        $vars_notnull = "";
        for ($i = 1; $i <= $slashCount; $i++) {
            $vars_notnull .= "'var$i'=>\$var" . $i . ",";
        }
        $vars_notnull = rtrim($vars_notnull, ",");
    }


    // Проверка наличия функции в контроллере
    $functionExists = false;
    $existingFunctionPattern = "/public function $functionName\((.*?)\)/";
    preg_match($existingFunctionPattern, $controllerContent, $existingFunctionMatches);

    if (!empty($existingFunctionMatches)) {
        $existingFunctionParams = $existingFunctionMatches[1];
        if ($existingFunctionParams !== $vars) {
            // Удаление существующей функции из контроллера
            $controllerContent = preg_replace("/public function $functionName\((.*?)\)\s*{.*?}\n/s", '', $controllerContent);
            echo "Функция $functionName с другим количеством параметров была удалена из контроллера.\n";
        } else {
            $functionExists = true;
            echo "Функция $functionName\n";
        }
    }

    // Создание контроллера, если он не существует
    if (strpos($controllerContent, "class $nameController") === false) {
        $controllerContent .= "\n\nclass $nameController extends Head {\n    // Код контроллера $controllerType\n}";
        echo "Контроллер $controllerType успешно создан и добавлен в файл.\n";
    }

    // Добавление функции в контроллер
    $existingFunctionPattern = "/public function $functionName\((.*?)\)\s*{.*?}\n/s";
    preg_match($existingFunctionPattern, $controllerContent, $existingFunctionMatches);

    $assets = "[{$is_api}{$react_use}{$vue_use}{$HTMX_use}{$HTMX_preloader_use}{$csp_nonce}]"; // Дополнительные настройки 

    $include = "";
    if (!$template_engine) {
        $include = "\n        include \"$folder/$fileName\";\n    ";
    }
    if (!$functionExists) {
        $functionContent = "    public function $functionName($vars) {\n        \$this->assets($assets);\n        " . malware($controllerType, $template_engine, $filesJson, $router, $vars_notnull) . "$include}\n";
        $controllerContent = preg_replace("/class $nameController extends Head \{/s", "class $nameController extends Head {\n$functionContent", $controllerContent);
        echo "Функция $functionName успешно добавлена в контроллер $controllerType.\n";
    }
    if (isset($existingFunctionMatches[0])) {
        $existingFunctionContent = $existingFunctionMatches[0];
        $newFunctionContent = "public function $functionName($vars) {\n        \$this->assets($assets);\n        " . malware($controllerType, $template_engine, $filesJson, $router, $vars_notnull) . "$include}\n";

        // Check if the first argument passed to str_replace is not null
        if ($existingFunctionContent !== null) {
            $controllerContent = str_replace($existingFunctionContent, $newFunctionContent, $controllerContent);
            echo "Данные для функции $functionName успешно обновлены в контроллере.\n";
        } else {
            echo "The first argument passed to str_replace is null.\n";
        }
    } else {
        echo "The array key does not exist.\n";
    }
   
}
if (isset($filesJson['include-in-controller']) && $filesJson['include-in-controller'] !== "") {
    $path = $filesJson['include-in-controller'];
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $include_file = "require '$path';";
    } else {
        file_put_contents($path, "<?php\nclass globals{\n/**\n *Сюда пишется код для передачи в контроллер.\n *Вы можете использовать все элементы OOP, но главное что-бы globals включал все вызываемые методы/переменные. \n*/\n}\n");
        $include_file = "require '$path';";
    }
} else {
    $path = 'routers/extend.php';
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $include_file = "require '$path';";
    } else {
        file_put_contents($path, "<?php\nclass globals{\n/**\n *Сюда пишется код для передачи в контроллер.\n *Вы можете использовать все элементы OOP, но главное что-бы globals включал все вызываемые методы/переменные. \n*/\n}\n");
        $include_file = "require '$path';";
    }
}
$asset_mainfest = "
    public \$token_csp;
    public \$assetsHead;
    /**
     * Генерирует необходимые js и css теги для подключения к необходимым библиотекам и файлам.
     *
     * @param array \$options ассоциативный массив опций.
     * @option bool `is_API` Выключает все файлы и библиотеки, и оставляет лишь чистый эндпоинт API в `process/API/{nameFile.php}`.   
     * `URL` вызова API вы указали сами как обычный путь к странице. По умолчанию `false`.
     * @option bool `react`
     *  Включает React JS и CSS файлы. По умолчанию `false`.
     * @option bool `vue`
     *  Включает Vue url путь к производственному CDN vue 3.4.21. По умолчанию `false`.
     * @option bool `HTMX`
     *   Включает HTMX v2 url путь к CDN. По умолчанию `false`.
     * @option bool `HTMX_preloader`
     *  Включает HTMX preload v2 url путь к CDN. По умолчанию `false`.
     * @option bool `csp_nonce` При включении, генерирует CSP nonce для подключения к библиотекам и файлам js, css. По умолчанию `false`.
     */
    protected function assets(array \$options = [
        'is_API'=>false,
        'react' => false,
        'vue' => false,
        'HTMX' => false,
        'HTMX_preloader' => false,
        'csp_nonce' => false,
    ]) 
    {
        static \$asset_mainfest=null;
        static \$css=null;
        static \$js=null;
        if(\$asset_mainfest===null){
            \$jsonData_index_page = file_get_contents(__DIR__.'/../asset/asset-manifest.json');
            if (\$jsonData_index_page === false) {
                die('Error reading asset-manifest.json');
            }
            \$asset_mainfest = json_decode(\$jsonData_index_page, true);
            if (\$asset_mainfest === null) {
                die('Error decoding JSON');
            }
            \$css=\$asset_mainfest['file']['css'];
            \$js=\$asset_mainfest['file']['js'];
        }
        \$is_API = \$options['is_API'] ?? false;
        \$react = \$options['react'] ?? false;
        \$vue = \$options['vue'] ?? false;
        \$HTMX = \$options['HTMX'] ?? false;
        \$HTMX_preloader = \$options['HTMX_preloader'] ?? false;
        \$csp_nonce = \$options['csp_nonce'] ?? false;
        
        if(\$is_API === false){
            \$nonce=null;

            if(\$csp_nonce === true){
                \$this->token_csp = bin2hex(random_bytes(16));
                \$nonce='nonce=\"'.\$this->token_csp.'\"';
                \$this->assetsHead .=  \"<meta property='csp-nonce' nonce={\$this->token_csp} />\\n\";
            }

            if(\$css !== null) {
                \$this->assetsHead .= \"\t   <link href='/static/css/\$css' rel='stylesheet' {\$nonce}>\\n\";
            }
            if(\$HTMX === true) {
                \$this->assetsHead .= \"\t   <script src='https://unpkg.com/htmx.org@2.0.0-alpha1/dist/htmx.min.js' {\$nonce}></script>\";
                if(\$HTMX_preloader === true) {
                    \$this->assetsHead .= \"\t   <script src='https://unpkg.com/htmx-ext-preload@2.0.0/preload.js' {\$nonce}></script>\";
                }
            }
            if(\$vue === true){
                \$this->assetsHead .= '\t   <script type=\"importmap\">{\"imports\":{\"vue\":\"https://unpkg.com/vue@3/dist/vue.esm-browser.prod.js\"} }</script>';
            }
            if(\$react === true) {
                \$this->assetsHead .= \"\t   <script type='module' src='/static/js/\$js' {\$nonce}></script>\";
            }
        }
    }
    /**
     * includeFile
     *
     * @param string \$file - путь к файлу, который нужно включить
     * @param array \$variables - массив переменных, которые нужно передать в файл
     * @return callable - функция, которая возвращает результат выполнения включенного файла
     *
     * Обратите внимание, что переменная \$file не будет учитываться в файле, который включается через include
     */
    public function includeFile(\$file, \$variables = []) {
        \$content = function() use (\$variables, \$file) {
            ob_start();
            extract(\$variables);
            include (\$file);
            return ob_get_clean();
        };
        return \$content;
    }
";
if (strpos($controllerContent, "<?php") === false) {
    $controllerContent = "<?php\n{$include_file}\nclass Head extends globals {" . $asset_mainfest . "\n" . "}" . $controllerContent . "";
}

file_put_contents(__DIR__ . '/../routers/controllers.php', $controllerContent);

echo "Страницы созданы!\n";

function collection()
{
    $pages = json_decode(file_get_contents(__DIR__ . '/../Pages.config.json'), true)['Pages'];

    $routes = [];

    foreach ($pages as $page) {
        $url = $page['url'];
        $controller = $page['controller'];
        $function = $page['name_func'];

        $pattern = "{$url}";
        $route = "{$controller}Controller@{$function}";

        $routes[$pattern] = $route;
    }

    $fileContent = "<?php return [\n";
    foreach ($routes as $pattern => $route) {
        $fileContent .= "    '{$pattern}' => '{$route}',\n";
    }
    $fileContent .= "];\n?>";

    file_put_contents(__DIR__ . '/../routers/collection.php', $fileContent, LOCK_EX);

    echo "Пути перезаписаны !\n";
}
collection();
