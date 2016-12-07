<?php
class App{

    public static function Init(){

        date_default_timezone_set('Europe/Moscow');
        db::getInstance()->Connect(
            Config::get('db_user'),
            Config::get('db_password'),
            Config::get('db_base'));

    }

}