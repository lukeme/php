<?php
class db {

    private static $instance;
    private static $dbh;

    function __construct() {
        $dsn = 'mysql:dbname=test;host=127.0.0.1;charset=utf8';
        $user = 'root';
        $pass = 'vertrigo';
        try {
            self::$dbh = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    static function query($sql, $is_ok = false, $fetch_one = false, $fetch_column= false) {
        self::getInstance();
        $sth = self::$dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $res = $sth->execute();
        if ($is_ok) {
            return $res > 0;
        }else if($fetch_column){
            return $sth->fetchColumn();
        } else {
            $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
            return $fetch_one ? $rows[0] : $rows;
        }
    }

    static function exec($sql) {
        return self::query($sql, true);
    }

    static function fetchOne($sql) {
        return self::query($sql, false, true);
    }

    static function fetchAll($sql) {
        return self::query($sql, false, false);
    }

    static function fetchField($sql) {
        return self::query($sql, false, true, true);
    }
}


//  example
header("Content-type:text/html, charset=utf8");
//$sql = "SELECT * FROM epub WHERE title LIKE '%安%'";
$month = date('m');
$day = date('d');
$sql = "SELECT script FROM `read_plan` WHERE `month` = {$month} AND `day` = {$day} limit 1";
$r = db::fetchAll($sql);
print_r($r);
$r = db::fetchOne($sql);
print_r($r);
$r = db::fetchField($sql);
print_r($r);
