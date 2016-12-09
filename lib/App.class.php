<?php
class App{

    public static function Init(){

        date_default_timezone_set('Europe/Moscow'); // устанавливаем временную зону для "скриптов"

        /*
         * Вызываем статический метод класса db, который создает и возвращает объект,
         * затем вызываем метод коннект этого объекта и передаем ему в качестве параметров
         * имена пользователя, пароль, имя базы, получив их через статический метод гет
         * класса Конфиг и передав в качестве параметров строки db_user db_password db_base
         * вопрос: почему именно такие строки? Гипотеза: потому что в конфиге, в котором мы храним
         * эти данные, данные строки используются в качестве имен ассоциативного массива, который по сути
         * и представляет собой конфиг. Гипотеза подтвердилась. Пошел смотреть что с db::getInstance...
         * А с db::getInstance все достаточно просто: этот метод создает объект db если он не был создан
         * и возвращает ссылку на него. Класс db построен по шаблону синглтон, что гарантирует создание
         * только единственного экземпляра класса. Ниже по коду вызывается метод Коннект этого единственного
         * экземпляра и ему передаются соответствующие параметры для создания коннекта, которые получаются через
         * использование статического метода гет класса конфиг. Пошел смотреть что этот класс из себя представляет..
         * 
         * */
        db::getInstance()->Connect(
            Config::get('db_user'),
            Config::get('db_password'),
            Config::get('db_base')
        );

        if (php_sapi_name() !== 'cli' && isset($_SERVER) && isset($_GET)){

            self::web(isset($_GET['path']) ? $_GET['path'] : '');

        }

    }

    protected static function web($url){

        $url = explode("/", $url);

        if(isset($url[0])){

            $_GET['page'] = $url[0];

            if(isset($url[1])){

                if(is_numeric($url[1])){

                    $_GET['id'] = $url[1];

                }

                else{

                    $_GET['action'] = $url[1];

                }

                if(isset($url[2])){

                    $_GET['id'] = $url[2];

                }

            }

        }

        else{

            $_GET['page'] = 'Index';

        }


        if(isset($_GET['page'])){

            $controllerName = ucfirst($_GET['page']) . 'Controller';
            $methodName = isset($_GET['action']) ? $_GET['action'] : 'index';
            $controller = new $controllerName();
            $data = [

                'content_data' => $controller->$methodName($_GET),
                'title' => $controller->title,
                'categories' => Category::getCategories(0)

            ];



            $view = $controller->view . '/' . $methodName . '.html';

            if(!isset($_GET['asAjax'])){

                $loader = new Twig_Loader_Filesystem(Config::get('path_templates'));
                $twig = new Twig_Environment($loader);
                $template = $twig->loadTemplate($view);

                echo $template->render($data);

            } else {

                echo json_encode($data);

            }

        }

    }

}