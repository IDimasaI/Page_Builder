<?php 
        if (preg_match('/{{foreach\((.*?)\)}}/', $templateContent, $matches)) {
            function extractForeach($html, $loopBody)
            {
                if (preg_match('/{{foreach\((.*?)\)}}/', $html, $matches)) {
                    $matches = explode(',', $matches[1]);
                    $result = '';
                    foreach ($matches as $match) {
                        $match = trim($match, "'");
                        $parts = explode('=>', $match);
                        $key = trim($parts[0], "'");
                        $value = trim($parts[1], "'");
                        $result .= str_replace('{{value}}', $value, $loopBody);
                    }
                    return $result;
                }
                return '';
            }
            $loopBody = '<p>{{value}}</p>';
            $templateContent = preg_replace_callback('/{{foreach\((.*?)\)}}/', function ($matches) use ($loopBody) {
                return extractForeach($matches[0], $loopBody);
            }, $templateContent);
        }

        
      
        if (preg_match_all('/{\%translate\(\'(.*?)\'\)\%}/', $templateContent, $matches)) {
            $lang='ru';
            if(preg_match('/{\$lang\(\'(.*?)\'\)\$}/', $templateContent, $matches)) {
                $lang=$matches[1];
                $templateContent = preg_replace('/{\$lang\(\'(.*?)\'\)\$}/', '', $templateContent);
            }
            $translateArray = null;
            function TranslateTemplate_dont_touch(string $text, string $lang)
            {
                   global $translateArray;
                   if ($lang !== 'en') {
                          if (!$translateArray) {
                                 include_once(__DIR__.'/../Translate/Translater.class.php');
                                 $t = Translater::init($lang, true);
                                 $t->put([
                                        "test1" => "тест1",
                                        "test2" => "тест2",
                                 ]);
                                 $translate = $translateArray['text'] = $t->getAll();
                          } else {
                                 $translate = $translateArray['text'];
                          }
                          return $translate[$text]??'Error Translate not found';
                   }else{return $text;}
            }
            $templateContent = preg_replace_callback('/{\%translate\(\'(.*?)\'\)\%}/', function ($match) use ($lang) {
                $key = $match[1];
                return TranslateTemplate_dont_touch($key, $lang);
            }, $templateContent);
        }