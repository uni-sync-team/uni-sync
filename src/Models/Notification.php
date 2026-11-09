<?php
// app/models/Notification.php
namespace App\Models;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use PDOStatement;

class Notification
{
    protected string $table = 'notification';
    protected string $primaryKey = 'idNotification';
    protected array $fillable = ['idUser', 'message', 'lu'];

    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Keep only fillable columns (protects against mass-assignment) */
    protected function filter(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function assertColumn(string $column): void
    {
        if ($column !== $this->primaryKey && !in_array($column, $this->fillable, true)) {
            throw new InvalidArgumentException("Colonne invalide : $column");
        }
    }

    public function findAll(?string $orderBy = null, string $dir = 'ASC'): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $this->assertColumn($orderBy);
            $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY $orderBy $dir";
        }
        return $this->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $row = $this->query(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]
        )->fetch();
        return $row ?: null;
    }

    public function findBy(string $column, mixed $value): array
    {
        $this->assertColumn($column);
        return $this->query("SELECT * FROM {$this->table} WHERE $column = ?", [$value])->fetchAll();
    }

    public function findOneBy(string $column, mixed $value): ?array
    {
        $this->assertColumn($column);
        $row = $this->query("SELECT * FROM {$this->table} WHERE $column = ? LIMIT 1", [$value])->fetch();
        return $row ?: null;
    }

    protected function insertRow(array $data): int
    {
        $data = $this->filter($data);
        if (!$data) {
            throw new InvalidArgumentException('Aucune donnée valide à insérer.');
        }
        $cols = implode(', ', array_keys($data));
        $marks = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO {$this->table} ($cols) VALUES ($marks)", array_values($data));
        return (int) $this->db->lastInsertId();
    }

    protected function updateRow(int $id, array $data): bool
    {
        $data = $this->filter($data);
        if (!$data) {
            return false;
        }
        $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($data)));
        $params = [...array_values($data), $id];
        return $this->query(
            "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?", $params
        )->rowCount() > 0;
    }

    public function create(array $data): int
    {
        return $this->insertRow($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateRow($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->query(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]
        )->rowCount() > 0;
    }

    public function count(): int
    {
        return (int) $this->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $total = $this->count();
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} LIMIT :l OFFSET :o"
        );
        $stmt->bindValue(':l', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }

    public function findByUser(int $idUser, bool $onlyUnread = false, int $limit = 50): array
    {
        $sql = "SELECT * FROM notification WHERE idUser = ?";
        if ($onlyUnread) {
            $sql .= " AND lu = 0";
        }
        $sql .= " ORDER BY dateCreation DESC LIMIT " . max(1, (int) $limit);
        return $this->query($sql, [$idUser])->fetchAll();
    }

    public function countUnread(int $idUser): int
    {
        return (int) $this->query(
            "SELECT COUNT(*) FROM notification WHERE idUser = ? AND lu = 0", [$idUser]
        )->fetchColumn();
    }

    public function markAsRead(int $idNotification, int $idUser): bool
    {
        return $this->query(
            "UPDATE notification SET lu = 1 WHERE idNotification = ? AND idUser = ?",
            [$idNotification, $idUser]
        )->rowCount() > 0;
    }

    public function markAllAsRead(int $idUser): int
    {
        return $this->query("UPDATE notification SET lu = 1 WHERE idUser = ? AND lu = 0", [$idUser])->rowCount();
    }

    /** Envoie la même notification à plusieurs utilisateurs */
    public function notifyMany(array $userIds, string $message): void
    {
        foreach ($userIds as $idUser) {
            $this->create(['idUser' => (int) $idUser, 'message' => $message]);
        }
    }
}
