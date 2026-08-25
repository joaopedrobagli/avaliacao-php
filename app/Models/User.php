<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Model = única camada que conversa com o banco.
 * Controllers nunca escrevem SQL direto; eles chamam métodos daqui.
 */
class User
{
    private PDO $db;

    public function __construct()
    {
        // Reaproveita a mesma conexão (Singleton) definida em Database.php
        $this->db = Database::getConnection();
    }

    /**
     * Busca um usuário ativo pelo email.
     * Usa prepared statement (o "?") para evitar SQL Injection:
     * o PDO nunca deixa o valor digitado virar parte do comando SQL.
     *
     * @return array|null Dados do usuário, ou null se não encontrar.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id_user, name, email, password
                FROM user
                WHERE email = ? AND ativo = 1
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch();

        // fetch() retorna "false" quando não acha nada.
        // Convertendo pra null deixa o retorno mais claro pra quem usa.
        return $user ?: null;
    }

    /**
     * Cria um novo usuário (usado na tela "Cadastrar usuário" do wireframe).
     * A senha já deve chegar aqui em texto puro; quem faz o MD5 é este
     * método, para centralizar a regra de "como a senha é guardada"
     * dentro do Model (o Controller não deveria precisar saber disso).
     *
     * @return int ID do usuário recém-criado.
     */
    public function create(string $name, string $email, string $password): int
    {
        $sql = 'INSERT INTO user (name, email, password, ativo)
                VALUES (?, ?, ?, 1)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $name,
            $email,
            md5($password), // VARCHAR(45) não comporta bcrypt (60 chars)
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Verifica se já existe um usuário com este email.
     * Útil para validar duplicidade antes de cadastrar.
     */
    public function emailExists(string $email): bool
    {
        $sql = 'SELECT id_user FROM user WHERE email = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);

        return (bool) $stmt->fetch();
    }
}
