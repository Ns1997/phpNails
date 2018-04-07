<?php
/*Сущности*/
//Заказ
class Order {

    //уникальный id - будет генерироваться БД при вставке строки
    protected $id;
    //дата создания заказа
    protected $created_at;
    //имя заказчика
    protected $name;
    //телефон заказчика
    protected $phone;
    //желаемая дата
    protected $desired_date;
    //желаемое время
    protected $desired_time;
    //комментарий
    protected $comment;

    //Конструктор класса с двумя параметрами со значениями по умолчанию в конце списка параметров
    function __construct($name
    	, $phone
    	, $desired_date
    	, $desired_time
    	, $comment
    	, $id = 0
    	, $created_at = '') {

        $this->id = $id;
        $this->name = $name;
        $this->phone = $phone;
        $this->desired_date = $desired_date;
        $this->desired_time = $desired_time;
        $this->comment = $comment;
        $this->created_at = $created_at;
    }

    //object -> DB
    //запись данных в БД
    function intoDb() {

        try {
            //Получаем контекст для работы с БД
            $pdo = getDbContext();
            //Готовим sql-запрос добавления строки данных о заказе в таблицу Order
            $ps = $pdo->prepare("INSERT INTO `Order` (name,phone,desired_date,desired_time,comment)
                                VALUES (:name,:phone,:desired_date,:desired_time,:comment)");
            
            //Превращаем объект в массив
            $ar = get_object_vars($this);
            //Удаляем из него первые два элемента - id и created_at
            array_shift($ar);
            array_shift($ar);
            
            //выполняем запрос к БД для добавления записи
            $ps->execute($ar);
        } catch (PDOException $e) {

            $err = $e->getMessage();

            if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation') {

                return 1062;

            } else {

                return $e->getMessage();
            }
        }
    }

    //DB -> object
    //Чтение одного заказа из БД (сейчас не используется, но может понадобиться в будущем)
    static function fromDB($id) {

        $order = null;

        try {
            //Получаем контекст для работы с БД
            $pdo = getDbContext();
            //Готовим sql-запрос чтения строки данных о заказе из таблицы Order
            $ps = $pdo->prepare("SELECT * FROM `Order` WHERE id = ?");
            
            //Пытаемся выполнить запрос на получение данных
            $resultCode = $ps->execute([$id]);
            //Если получилось - записываем данные в объект
            if ($resultCode) {
                
                $row = $ps->fetch();
                $order =
                    new Order(
                            $row['name']
                            , $row['phone']
                            , $row['desired_date']
                            , $row['desired_time']
                            , $row['comment']
                            , $row['id']
                            , $row['created_at']
                        );
                return $order;
            } else {
                $pdo->errorInfo();
                $pdo->errorCode();
                return new Order("", "", "");
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    //Получение списка всех заказов из БД
    static function GetOrders($date = '') {

        $ps = null;
        $orders = null;

        try {
            //Получаем контекст для работы с БД
            $pdo = getDbContext();
            //Готовим sql-запрос чтения всех строк данных о заказах из таблицы Order,
            //отсортированных от более новых к более старым
            if ($date === '') {
                //echo "1 - $date";
                $ps = $pdo->prepare("SELECT * FROM `Order` ORDER BY `created_at` DESC");
                //Выполняем
                $ps->execute();
            } else {
                //echo "2 - $date";
                $ps = $pdo->prepare("SELECT * FROM `Order` WHERE `desired_date` = ? ORDER BY `created_at` DESC");
                //Выполняем
                $ps->execute([$date]);
            }

            
            //Сохраняем полученные данные в ассоциативный массив
            $orders = $ps->fetchAll();

            return $orders;
        } catch (PDOException $e) {

            echo $e->getMessage();
            return false;
        }
    }
}