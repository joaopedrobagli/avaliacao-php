<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Model = única camada que conversa com o banco.
 * Aqui ficam todas as regras de ACESSO A DADOS de "service".
 * Regras de negócio que não são "dado" (tipo cálculo de comissão)
 * também moram aqui, porque são específicas do domínio "service" -
 * mas o Controller é quem decide QUANDO chamar.
 */
class Service
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Busca serviços aplicando filtros opcionais (todos combináveis).
     * Isso atende aos 4 cenários de filtro do BDD: período, nome do
     * serviço, status e nome do usuário.
     *
     * @param array $filtros Chaves possíveis: 'data_inicio', 'data_fim',
     *                       'descricao', 'status' ('pendente'|'finalizado'),
     *                       'usuario'
     */
    public function findAll(array $filtros = []): array
    {
        // Join com "user" porque a tabela precisa mostrar o nome do
        // usuário (não só o ID) na tabela do Dashboard.
        $sql = 'SELECT s.id_service, s.description, s.price, s.created_at,
                       s.finished_at, s.commission_user,
                       u.name AS user_name
                FROM service s
                INNER JOIN user u ON u.id_user = s.user_id_user
                WHERE 1=1';

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= ' AND s.created_at >= ?';
            $params[] = $filtros['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= ' AND s.created_at <= ?';
            $params[] = $filtros['data_fim'] . ' 23:59:59';
        }

        if (!empty($filtros['descricao'])) {
            $sql .= ' AND s.description LIKE ?';
            $params[] = '%' . $filtros['descricao'] . '%';
        }

        if (!empty($filtros['status'])) {
            // Não existe coluna "status": é derivado de finished_at.
            // finished_at NULL => pendente | finished_at preenchido => finalizado
            if ($filtros['status'] === 'pendente') {
                $sql .= ' AND s.finished_at IS NULL';
            } elseif ($filtros['status'] === 'finalizado') {
                $sql .= ' AND s.finished_at IS NOT NULL';
            }
        }

        if (!empty($filtros['usuario'])) {
            $sql .= ' AND u.name LIKE ?';
            $params[] = '%' . $filtros['usuario'] . '%';
        }

        $sql .= ' ORDER BY s.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT s.*, u.name AS user_name, u.email AS user_email
                FROM service s
                INNER JOIN user u ON u.id_user = s.user_id_user
                WHERE s.id_service = ?
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Cria um serviço novo, sempre com status implícito "Pendente"
     * (ou seja, finished_at = NULL).
     */
    public function create(string $description, float $price, int $userId): int
    {
        $sql = 'INSERT INTO service (description, price, user_id_user)
                VALUES (?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$description, $price, $userId]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $description, float $price): bool
    {
        $sql = 'UPDATE service SET description = ?, price = ? WHERE id_service = ?';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$description, $price, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM service WHERE id_service = ?');

        return $stmt->execute([$id]);
    }

    /**
     * Marca o serviço como finalizado: grava finished_at (agora) e
     * calcula/grava a comissão. Retorna os dados atualizados, que o
     * Controller usa para montar o email.
     */
    public function finish(int $id): ?array
    {
        $service = $this->findById($id);

        if (!$service || $service['finished_at'] !== null) {
            // Já finalizado ou não existe: não faz nada.
            return null;
        }

        $comissao = $this->calcularComissao((float) $service['price']);

        $sql = 'UPDATE service
                SET finished_at = NOW(), commission_user = ?
                WHERE id_service = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$comissao, $id]);

        return $this->findById($id);
    }

    /**
     * Regra de comissão do enunciado:
     *   - até R$ 250,00      -> 5%
     *   - acima de R$ 1.000  -> 10%
     *   - acima de R$ 10.000 -> 20%
     *
     * SUPOSIÇÃO (documentada): o enunciado não define a faixa entre
     * R$ 250,01 e R$ 1.000,00. Optamos por manter 5% nessa faixa
     * (leitura mais conservadora), até confirmação do avaliador/cliente.
     */
    public function calcularComissao(float $valor): float
    {
        if ($valor > 10000) {
            $percentual = 0.20;
        } elseif ($valor > 1000) {
            $percentual = 0.10;
        } else {
            // Cobre 0 até 1000 (inclui a faixa não especificada 250-1000)
            $percentual = 0.05;
        }

        return round($valor * $percentual, 3);
    }

    /**
     * Soma o valor de TODOS os serviços prestados por um usuário
     * (independente de status), para o card "Valor Total" do Dashboard.
     */
    public function totalByUser(int $userId): float
    {
        $sql = 'SELECT COALESCE(SUM(price), 0) AS total
                FROM service
                WHERE user_id_user = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return (float) $stmt->fetchColumn();
    }

    /**
     * Últimos serviços PENDENTES de um usuário, para a listinha
     * destacada do Dashboard. Limita a quantidade (padrão 5).
     */
    public function pendingByUser(int $userId, int $limit = 5): array
    {
        $sql = 'SELECT id_service, description, price
                FROM service
                WHERE user_id_user = ? AND finished_at IS NULL
                ORDER BY created_at DESC
                LIMIT ' . (int) $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }
}
