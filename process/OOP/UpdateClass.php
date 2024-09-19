<?php
$array = json_decode(file_get_contents('class_paths.json'), true);

$newClassPaths = 'private $classPaths = [' . PHP_EOL;
$entries = [];

foreach ($array as $key => $value) {
    $entries[] = "        '$key' => '$value'";
}

$newClassPaths .= implode("," . PHP_EOL, $entries) . PHP_EOL;
$newClassPaths .= '    ];'; // Пример объекта

$filePath = 'autoloader.php';
$fileContent = file_get_contents($filePath);

// Находим место для вставки нового объекта
$position = strpos($fileContent, 'private $classPaths = ');
if ($position !== false) {
    // Находим позицию закрывающей скобки
    $closingBracketPosition = strpos($fileContent, ';', $position);
    if ($closingBracketPosition !== false) {
        // Заменяем существующий объект на новый массив
        $newContent = substr_replace($fileContent, $newClassPaths, $position, $closingBracketPosition - $position + 1);

        // Перезаписываем файл с новым содержимым
        file_put_contents($filePath, $newContent);
        echo 'Массив успешно перезаписан в $classPaths.';
    }
}
?>