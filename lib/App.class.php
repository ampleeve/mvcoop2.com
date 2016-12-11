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
         * UPD: посмотрел. В общем, класс прикольный, кеш использует в методе получения конфигурационного массива.
         * Сам метод гет как раз, который здесь вызывается использует этот метод получения конфигурационного
         * массива, проверяет есть ли этот параметр вообще в конфиге
         *  и возвращает значение соответствующего параметра ассоциативного массива если он существует
         * (в противном случае выбрасывает эксепшн).
         * */
        db::getInstance()->Connect(
            Config::get('db_user'),
            Config::get('db_password'),
            Config::get('db_base')
        );
        // здесь происходит проверка что скрипт отрабатывает действительно
        // в вебе, а не в консольном приложении. Судя по коду, для этого
        // обязательно должны существовать массивы сервер и гет, а также
        // функция php_sapi_name не должна возвращать значение "cli". Если это условие дает истину, то выполняется код
        // внутри иф. А имеено - вызывается собственный статический метод web и в качестве параметра
        // ему передается либо значение из массива гет с индексом "path" (если оно существует).
        // Либо передается пустая строка.
        if (php_sapi_name() !== 'cli' && isset($_SERVER) && isset($_GET)){

            self::web(isset($_GET['path']) ? $_GET['path'] : '');

        }

    }

    /**
     * @param $url
     * Ну что же, начнем разбирать метод web. Он достаточно интересен )). Статический и защищенный,
     * что означает что обращаться к нему могут только наследники класса App. Видно, что переданный параметр
     * разделяется знаком слеша. Сразу стало интересно посмотреть: а что будет если вывести урл до и после эксплоуда?
     * А получилось не особо интересно: сначала пустая строка, затем идет уже моссив с одним элементом
     * в нашем контексте - собственно строкой. Видимо он рассчитан на то, что на вход приходит строка в виде урла с разделителями
     */
    protected static function web($url){


        //echo '<pre>';
        //var_dump($url);
        //echo '</pre>';

        $url = explode("/", $url);

        //echo "<pre>";
        //var_dump($url);
        //echo "</pre>";die();

        if(isset($url[0])){     // если есть хотя бы один элемент в массиве, то

            //echo "<pre>";
            //var_dump($url[0]);
            //echo "</pre>";die();

            // установить в гет массив с индексом пэйдж это значение
            $_GET['page'] = $url[0];    // в нашем контексте устанавливается пустая строка

            if(isset($url[1])){ // если заданы 2 элемента массива (есть хотя бы один слеш в исходной строке)

                if(is_numeric($url[1])){    // число ли второй элемент массива?

                    $_GET['id'] = $url[1];  // если да, то в id записывается число

                }

                else{

                    $_GET['action'] = $url[1];  //  иначе записывается в экшн - т.е. строка не число

                }

                if(isset($url[2])){     // если установлен третий элемент (т.е. 2 слеша есть в исходной строке)

                    $_GET['id'] = $url[2];  // то это уже точно ид и мы его присваиваем в ид гет

                }

            }

        }

        else{       // если нулевой элемент не установлен, то в пейдж записываем индекс

            $_GET['page'] = 'Index';

        }


        if(isset($_GET['page'])){       // если установлен пейдж

            // в нашем контексте устанавливается имя контроллера "Controller", т.к.
            // page содержит значение "".
            $controllerName = ucfirst($_GET['page']) . 'Controller';

            // в нашем контексте устанавливается имя экшена "index", т.к.
            // action не определен.
            $methodName = isset($_GET['action']) ? $_GET['action'] : 'index';

            // в нашем контексте создается объект класса "Controller", т.к.
            // описание класса в Controller.class.php. Пойду посмотрю чего там в нем..
            //Так, проверил - там есть свойство вью, которое по умолчанию содержит админ, вернулся сюда вывести его
            // и проверить что так и есть. Да, проверил, так и есть. Пошел опять туда смотреть что там еще есть...
            //Есть непонятный экшн индекс, который получает данные, а возвращает как бы пустой массив.
            // Видно что создается массив $data, в него заносится контент дата, который вернет пустой массив, т.к.
            // хоть и предается массив гет, возвращает то он в любом случае только пустой массив всегда.
            // тайтл - присваивается тайтл Интеренте-магазин по сути
            // Категориям присваивается геткатегориес, пошел смотреть их в класс категори..Ну да, получаются в нем категории
            // с парент ид 0 и статусом эктив
            $controller = new $controllerName();
            //echo "<pre>";
            //var_dump($controller->view);
            //echo "</pre>";die();
            $data = [
                // вызовется контроллера метод индекс и ему передастся массив гет, но
                // массив гет никак не используется
                'content_data' => $controller->$methodName($_GET),
                // тайтл берется чере конфигуратора класс из конфига
                'title' => $controller->title,
                // категории берутся из класса категории с использованием модели
                'categories' => Category::getCategories(0)

            ];


            //В нашем контексте сюда присвоится admin/index.html
            $view = $controller->view . '/' . $methodName . '.html';
            //echo '<pre>';
            //var_dump($view);
            //echo '</pre>';die();

            if(!isset($_GET['asAjax'])){    // данный блок срабатывает если в запросе не передан параметр asAjax

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