<?php
spl_autoload_register(function ($class) {
    echo "-";
    $class= str_replace('\\', '/', $class);
    $class= str_replace('Djs', 'src', $class);
    include $class.'.php';
});
?>