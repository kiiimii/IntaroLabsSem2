<?php

namespace BazaraJack\Library\Model;

use PDO;
use PDOException;

final class Database{
    private static $instance = null;

    private function __construct(){}

    public static function getConnection():PDO {
        if(!self::$instance){
            try{
                $dsn = "mysql:host=db;dbname=library;charset=utf8mb4";
                $user = 'root';
                $pass = 'root';

                self::$instance = new PDO($dsn,$user,$pass,[
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }catch(PDOException $e){
                die("Ошибка подключения к БД: " . $e->getMessage());
            }
        }

        return self::$instance;
    }



}