<?php
abstract class Model{

    protected static $table; // Объявляем свойство $table - вопрос - Для чего?

    protected static $properties = [    // создаем двумерный массив свойств... Для чего?

        'id' => [                       // Для чего нужен id? Где он используется?

            'type' => 'int',
            'autoincrement' => true,
            'readonly' => true,
            'unsigned' => true          // Похоже что-то на создание столбца в таблице, но в нашем контексте все равно не понятно

        ],

        'created at' => [

            'type' => 'datetime',
            'readonly' => true

        ],

        'updated at' => [

            'type' => 'datetime',
            'readonly' => true

        ],

        'status' => [

            'type' => 'int',
            'size' => 2,
            'unsigned' => true

        ],

    ];

    public function __construct(array $values){    // Слово array для чего? Явно указать что здесь массив?

        static::setProperties();                    // Я так понимаю, переопределяется этот метод в потомках

        foreach ($values as $key => $value){        // Посмотрю где по коду создаются потомки и что там им в конструктор передается

            $this->$key = $value;

        }

    }

    protected static function setProperties(){

        return true;                                // Никогда не сработает в нашем контексте?

    }

    public final static function generate(){

        if(self::tableExists()){

            throw new Exception('Table already exists');

        }
        static::setProperties();
        $query = 'CREATE TABLE ' . static::$table . ' (';

        foreach (static::$properties as $property => $params){

            if(!isset($params['type'])){

                throw new Exception('Property ' . $property . 'has no type');

            }

            $query .= ' `' . $property . '`';
            $query .= ''.$params['type'];

            if(isset($params['size'])){

                $query .= '(' .$params['size'] . ')';

            }

            if(isset($params['unsigned']) && $params['unsigned']){

                $query .= ' UNSIGNED';

            }

            if( isset($params['autoincrement']) && $params['autoincrement']){

                $query .= ' AUTO_INCREMENT';

            }

            $query .= ',' . "\n";

        }

        $query .= 'PRIMARY KEY (`id`)';
        db::getInstance()->Query($query);
        return true;

    }

    public function __get($name){

        $this->checkProperty($name);
        $return = null;

        switch (static::$property['type']){

            case 'int':
                return (int)$this->$name;

            default:
                return (string)$this->$name;
        }

    }

    public function __set($name, $value){

        $this->checkProperty($name);
        switch(static::$properties[$name]['type']){

            case 'int':
                $this->$name = (int)$value;
                break;

            default:
                $this->$name = (string)$value;
                break;
        }
        if(isset(static::$properties[$name]['size'])){
            $this->$name = mb_substr($this->$name, 0, static::$properties[$name]['size']);
        }

    }

    protected final static function tableExists(){

        return count(db::getInstance()->select('SHOW TABLES LIKE "' . static::$table . '"')) >0;

    }

    protected final function checkProperty($name){

        if(!isset(static::$properties[$name])){
            throw new Exception('Undefined property ' . $name);
        }
        if(!isset(static::$properties[$name]['type'])){
            throw new Exception('Undefined type for property ' . $name);
        }

    }

}
?>