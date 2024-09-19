<?php

/**
 * Шаблонизатор
 * */
class Template
{

    private $template;
    private $data;

    /**
     * @param string $template путь к шаблону
     * @param array|any $data данные для шаблона
     */
    public function __construct($template, $data)
    {
        $this->template = $template;
        $this->data = $data;
    }
    /**
     * @name pars_templates
     * @description Метод для замены строк {{template}} на функцию/строку заданную в $data как ключ(название), значение(функция)
     * @param string $templateContent содержимое шаблона
     * @param array|any $data данные для шаблона
     */
    private function pars_templates($templateContent, $data)
    {
        foreach ($data as $key => $value) {
            if (is_callable($value) && (strpos($templateContent, '{{' . $key . '}}') !== false || strpos($templateContent, '@{{' . $key . '}}') !== false)) {
                $value = preg_replace("/\t|\n|\s+/", ' ', call_user_func($value));
                $templateContent = str_replace(['@{{' . $key . '}}', '{{' . $key . '}}'], [$value, $value], $templateContent);
            } elseif (strpos($templateContent, '{{' . $key . '}}') !== false || strpos($templateContent, '@{{' . $key . '}}') !== false) {
                $templateContent = str_replace(['@{{' . $key . '}}', '{{' . $key . '}}'], [$value, $value], $templateContent);
            }
        }

        return $templateContent;
    }

    /**
     * Метод для рендеринга шаблона.
     * @param array $vars данные роутера для страницы php
     * @return string полученное содержимое шаблона
     */
    public function render($vars = [])
    {
        //Буферизация вывода- выполнение php кода и получение результата в виде строки
        // начало буферизации вывода
        ob_start();

        // распаковка данных в массив
        extract($vars);

        // подключение и обработка шаблона
        include($this->template);

        // получение содержимого буфера и очистка буфера
        $templateContent = ob_get_clean();
        // замена переменных в содержимом шаблона
        $templateContent = $this->pars_templates($templateContent, $this->data);
        //include('experemental.class.php');
        return $templateContent;
    }
    /**
     * @name InnerScriptRender
     * @description Метод для рендера шаблонов с добавлением компонентов для InnerScript
     */
    public function InnerScriptRender($vars = [], $data_pages = null)
    {
        //Буферизация вывода- выполнение php кода и получение результата в виде строки
        // начало буферизации вывода
        $data_pages = json_encode($data_pages);
        ob_start();

        // распаковка данных в массив
        extract($vars);

        // подключение и обработка шаблона
        include($this->template);

        // получение содержимого буфера и очистка буфера
        $templateContent = ob_get_clean();


        // замена переменных в содержимом шаблона
        $templateContent = $this->pars_templates($templateContent, $this->data);

        // модификация тега body
        $data_pages !== null ?
        
            $templateContent = preg_replace_callback('/<body(.*?)>(.*?)<\/body>/s', function ($matches) use ($data_pages) {
                $data_pages !== null ?
                    $data_pages = "<div id='app' data-page='{$data_pages}'></div>" :
                    $data_pages = null;

                $body_atributes = $matches[1] ?? null;
                return "<body{$body_atributes}>{$data_pages}{$matches[2]}</body>";
            }, $templateContent)
            : null;

        return $templateContent;
    }
}
