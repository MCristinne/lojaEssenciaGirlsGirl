<?php
class Conexao {
    private static ?PDO $instancia = null;

    public static function getConexao(): PDO {
        if (self::$instancia === null) {
            $host = 'localhost';
            $db = 'loja_essencia_girls_girl'; 
            $user = 'root';
            $pass = '';

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            self::$instancia = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }
        return self::$instancia;
    }
}