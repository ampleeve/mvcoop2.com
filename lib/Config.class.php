<?php

/**
 * Class Config
 * Я добрался до доктайпа этого класса, т.к. его метод вызывается в классе App, который на сколько я понял, инициализирует
 * приложение. Вызывается его метод гет в контексте создания подключения к бд. Пока есть сомнения относительно того
 * - один ли раз создается подключение к бд... Скорее всего один, иначе в чем прикол не ясно. Или точнее подключение
 * создается много раз, но в коде оно происходит только в одном месте.
 */
class Config{


    // Гипотеза: свойство статическое, в котором кешируется текущий конфиг. Создается при создании объекта класса.
    // затем в него заносится массив конфигурационный, который в нашем контексте находится в файле config.default.php.
    // По коду ниже будет понятно, что берется именно этот файл, т.к. других не существует. Вообще этот файл по коду имеет самый
    // низкий приоритет в подключении. Сначала идет файл config.prod.php, затем config.dev.php и только потом дефолтный, но по
    // коду это ниже. Да, кстати, объект этого класса создается при первом обращении к его методам, т.к. у нас объявлен автозагрузчик
    // в самой первой строке нашего приложения app.php, путем подключения файла autoload.php, который в себе и содержит
    //регистрацию автозагрузчика и саму функцию автозагрузчика.
    // Что интересно - это то, что это оформлено не в виде классов(файлы app.php autoload.php).
    private static $configCache = [];

    // тот самый метод, который используется для получения имени пользователя бд и тд. Вызывается он в первый раз
    // в нашем контексте в момент создания подключения к бд при вызове метода инит класса апп. в качестве параметра
    // передается имя пары из ассоциативного массива, который объявлен и заполнен в файле config.default.php.
    public static function get($parameter){

        // вот эта строка очень интересная - проверяет - установлен ли переданный параметр.
        // По сути если параметр установлен в файле config.default.php(т.е. есть название переданного
        // параметра в ассоциативном массиве), то блок внутри иф
        // не сработает. Как я мыслю: значит self::getCurrentConfiguration() (гипотеза)
        // вернет ссылку на массив ассоциативный в файле конфиг дефолт пхп. Затем, подставится
        // параметр и уже проверится существует ли такой элемент в ассоциативном массиве. Если нет, то
        // сработает блок внутри иф. Сейчас проверю: если передать в качестве параметра в методе инит класса апп
        // не db_user, а db_user1, то высветится текст "Parameter db_user1 does not exists". Подтвердилось. Так и есть.
        // Теперь пойду разбираться с работой метода getCurrentConfiguration()..
        if (!isset(self::getCurrentConfiguration()[$parameter])) {
            throw new Exception('Parameter ' . $parameter . ' does not exists');
        }

        return self::getCurrentConfiguration()[$parameter];

    }

    /**
     * @return array
     * @throws Exception
     * Добрался я сюда в процессе разбора статического метода гет, который используется в классе апп в методе инит
     * для подключения к бд. вызывается этот метод первый раз при проверке - существует ли параметр db_user. Сейчас
     * появилась мысль что метод вызывается дважды и не понятно зачем. Ведь можно было сохранить один раз значение.
     * Попробую переписать. Переписал - по-моему лучше, но с точки зрения производительности разницы, наверное, не особо,
     * спросил в уроке
     * UPD: После изучения понял что метод прикольный ) Отрабатывает один раз по сути и кеширует у себя весь массив
     * конфигурационный. При повторном обращении возвращает кеш.
     */
    private static function getCurrentConfiguration(){

        // здесь проверяется - пустой ли кеш. Гипотеза: в нашем контексте он пустой всегда.
        // А вообще-то может и нет, ведь там несколько вызовов метода. У нас обращение к данному методу
        // точно происходит не один раз, а несколько. Соответственно, если прописать внутри блока иф трассировку
        // то она отработает только один раз, благодаря статическому свойству кеш. Сейчас проверю.
        // Проверил - все ок, кеш работает
        if(empty(self::$configCache)){

            //echo 'пустой кеш';
            // все ясно как в божий день: __DIR__ возвращает путь к папке, в которой располагается файл, в котором и
            // написано это "__DIR__". В данном случае, это lib папка
            $configDir = __DIR__ . '/../configuration';
            $configProd = $configDir . 'config.prod.php';
            $configDev = $configDir . 'config.dev.php';
            $configDefault = $configDir . '/config.default.php';

            if(is_file($configProd)){

                require_once $configProd;

            } else if(is_file($configDev)){

                require_once $configDev;

            } else if(is_file($configDefault)){

                require_once $configDefault;

            }else{

                throw new Exception('Не найден файл конфигурации');

            }

            if(!isset($config) ||  !is_array($config)){

                throw new Exception('Unable to load configuration. Не загружается файл конфигурации');

            }

            self::$configCache = $config;

        }

        return self::$configCache;

    }

}


?>


