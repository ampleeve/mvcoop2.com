<?php
//подключаем автозагрузчик twig и запускаем статический метод register
require_once 'lib/Twig/Autoloader.php';
Twig_Autoloader::register();

//Регистрируем собственный автозагрузчик
spl_autoload_register("gbStandardAutoload");

function gbStandardAutoload($className){

    // Папки с классами для загрузки
    $dirs = [

        'controller',
        'data/migrate',
        'lib',
        'lib/smarty',
        'lib/commands',
        'model/'

    ];

    $found = false;
    //Имя файла формируется из имени класса и '.class.php'
    foreach ($dirs as $dir){

        $filename = __DIR__ . '/' . $dir . '/' . $className . '.class.php';

        if(is_file($filename)){

            require_once ($filename);
            $found = true;

        }

    }

    if (!$found){

        throw new Exception('Нет файла классса для загрузки: ' . $className . $filename);

    }

    return true;

}