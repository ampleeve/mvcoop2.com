<?php
class db{

    private static $_instance = null;

    private $db;

    public static function getInstance(){

        if(self::$_instance == null){

            self::$_instance = new db();

        }

        return self::$_instance;

    }

    private function __construct(){}
    private function __sleep(){}
    private function __wakeup(){}
    private function __clone(){}

    public function Connect($user,
                            $password,
                            $base,
                            $host = 'localhost',
                            $port = 3306){

        $connectString = 'mysql:host=' . $host . ';port= ' . $port . ';dbname=' . $base . ';charset=UTF8;';

        $this->db = new PDO($connectString, $user, $password,
                            [

                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // возвращать ассоциативные массивы
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION       // возвращать Exception в случае ошибки

                            ]
        );


    }

    public function Query($query, $params = array()){

        $res = $this->db->prepare($query);
        $res->execute($params);
        return $res;

    }

    public function Select($query, $params = array()){

        $result = $this->Query($query, $params);

        if($result){

            return $result->fetchAll();

        }

    }

}
?>