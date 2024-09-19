<?php

class Translater {
    private static $instancesCount = 0;

    private $lang = 'en';

    private $translations = [];

    private static $instances = [];

    private function __construct(string $lang, bool $loadTranslations=false)
    {
        self::$instancesCount++;
      
        $this->lang = $lang;
        if ($loadTranslations===true&&$this->lang !== 'en') {
            $this->loadTranslations();
        }
    }

    public static function init(string $lang = 'en', bool $loadTranslations = false)
    {
        if (!isset(self::$instances[$lang])) {
            self::$instances[$lang] = new self($lang, $loadTranslations);
        }

        return self::$instances[$lang];
    }

    private function loadTranslations()
    {
        $filePath = __DIR__ . "/../../Storage/languages/" . $this->lang . ".php";
        if (file_exists($filePath)) {
            $this->translations[$this->lang] = include $filePath;
        }
    }

    public function translate(string $text): string
    {
        if ($this->lang == 'en') {
            return $text;
        } elseif (isset($this->translations[$this->lang][$text])) {
            return $this->translations[$this->lang][$text];
        } else {
            return 'Error Translate not found';
        }
    }

    public function put(array $text): void
    {
        if ($this->lang == 'en') {
            return;
        };
        $this->translations[$this->lang] = array_merge($this->translations[$this->lang]??[], $text);
    }

    public function getAll(): array
    {
        return $this->translations[$this->lang] ?? [];
    }
}
