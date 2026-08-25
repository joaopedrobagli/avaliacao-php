<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Responsável apenas por criar e devolver a conexão PDO.
 * Ajuste as credenciais abaixo conforme seu ambiente local.
 */
class Database
{
    private static ?PDO $connection = null;

    private const HOST = '127.0.0.1';
    private const DBNAME = 'os_jm';
    private const USER = 'root';
    private const PASS = '';
    private const CHARSET = 'utf8mb4';

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=' . self::CHARSET;

            try {
                self::$connection = new PDO($dsn, self::USER, self::PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Em produção isso deveria ir para um log, não pra tela.
                die('Erro ao conectar ao banco: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
