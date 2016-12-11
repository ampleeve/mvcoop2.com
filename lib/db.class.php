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

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * До данного метода я добрался, т.к. разбирал метод селект, сюда передается запрос и некие параметры
     * в виде массива. Смотрим что делает данный метод. В нашем случае он получит запрос
     * SELECT id_category, name FROM categories WHERE status=:status AND parent_id =:parent_id
     * и в качестве параметров: 'status' => Status::Active(берется значение из класса
     * статус где эктив соответствует единице),
     * 'parent_id' => $parentId
     */
    public function Query($query, $params = array()){
        // переменной рез присваивается значение метода препэйр и ему передается запрос. Похоже что ему присваивается даже
        // не значение, а сам объект. Сейчас выведем его и посмотрим..Да, действительно это объект PDO. В нем, после выполнения
        // $res = $this->db->prepare($query); только одно значение в свойстве ["queryString"]=>
        //string(87) "SELECT id_category, name FROM categories WHERE status=:status AND parent_id =:parent_id"
        // После использования метода экзекъют тоже никаких изменений. По сути возвращается строка запроса

        $res = $this->db->prepare($query);
        $res->execute($params);
        //echo '<pre>';
        //var_dump($res);
        //echo '</pre>';die();
        return $res;

    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     * Добрался я сюда т.к. в классе категорий вызывается данный метод для получения категорий. Видим что ему передается запрос
     * и параметры какие-то и сразу показано что параметры - это массив. В нашем конкретном случае передается запрос
     * "SELECT id_category, name FROM categories WHERE status=:status AND parent_id =:parent_id" что означает
     * верни ид категории и имя для тех значений в таблице категориес, в которых одинаковые статусы и одинаковые перент ид(гипотеза).
     *В качестве параметров передается массив ассоциативный, в котором 'status' => Status::Active(берется значение из класса
     * статус где эктив соответствует единице),
     * 'parent_id' => $parentId (передается значение в вызываемый этот метод метод).
     * Далее мы вимдим что вызываетсяметод query данного класса, пошел его смотреть. В общем, здесь речь идет
     * работе с PDO - стандартная библиотека для работы с бд. Функция fetchAll - возвращает уже подготовленные данные.
     */
    public function Select($query, $params = array()){

        $result = $this->Query($query, $params);
        //echo '<pre>';
        //var_dump($result->fetchAll());
        //echo '</pre>';die();

        if($result){

            return $result->fetchAll();

        }

    }

}
?>