<?php
$function = $argv[1];

if($function=='compile'){
    echo "-------\n";
    exec('php commands/compile.php && php commands/compile.php');
    echo "==>Файлы успешно обновлены!\n";
    echo "-------\n";
}
if($function=='Page_builder'){
    echo "-------\n";
    include('Page_builder.php');
    echo "-------\n";
}
?>
