<?php

class Conexao {
    private static ?PDO $instance = null;

    public static function getConn(): PDO {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=agencia;charset=utf8",
                    "root",
                    ""
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Erro ao conectar ao banco de dados: " . htmlspecialchars($e->getMessage()));
            }
        }

        return self::$instance;
    }
}

$pdo = Conexao::getConn();
?>