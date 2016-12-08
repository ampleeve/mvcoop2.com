<?php
require_once 'autoload.php';
// подключаем файл с методами автозагрузки классов

try{
    App::init();
}
catch (PDOException $e){
    echo "DB is not available";
    var_dump($e->getTrace());
}
catch (Exception $e){
    echo $e->getMessage();
}