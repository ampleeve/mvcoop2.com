<?php
class db{

    /*
     * здесь мы создаем пустое статическое свойство, т.е. оно не будет каждое свое у
     * объектов класса, значение одно на все экземпляры класса
     * */
    private static $_instance = null;
    /*
     * Далее создаем приватное свойство db, которое доступно только внутри данного класса, т.к. приватное
     * */
    private $db;
    /*
     * Далее создаем публичный статичный метод getInstance(шаблон синглтон),
     * т.е. к нему можно обращаться извне через имя класса::getInstance(),
     * что и делается в классе App. В данном методе проверяется сначала
     * существует ли объект db. Если не существует, то он создается. Затем он возвращается.
     * Здесь я вижу смысл провести трассировку его создания
     * */
    public static function getInstance(){

        if(self::$_instance == null){
            /*
             * если выполнить закомментированный код над созданием и после создания объекта db,
             * то мы увидим что сначала выведется Null, а затем уже пустой объект, без данных.
             * */
            //echo "<pre>";
            //var_dump(self::$_instance);
            //echo "</pre>";
            self::$_instance = new db();
            //echo "<pre>";
            //var_dump(self::$_instance);
            //echo "</pre>";die();

        }

        return self::$_instance; // здесь возвращается объект db

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