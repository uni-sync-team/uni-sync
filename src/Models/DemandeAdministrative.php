<?php
namespace App\Models;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use PDOStatement;

class DemandeAdministrative
{
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_TRAITEE    = 'TRAITEE';
    public const STATUT_REFUSEE    = 'REFUSEE';

    protected string $table = 'demandeAdministrative';
    protected string $primaryKey = 'idDemande';
    protected array $fillable = ['idEtudiant', 'idAdmin', 'typeDemande', 'dateDemande', 'statut'];

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

    public function findByEtudiant(int $idEtudiant): array
    {
        return $this->query(
            "SELECT * FROM demandeAdministrative WHERE idEtudiant = ? ORDER BY dateDemande DESC", [$idEtudiant]
        )->fetchAll();
    }

    public function findByStatut(string $statut): array
    {
        return $this->query(
            "SELECT d.*, e.cne, e.nom, e.prenom
             FROM demandeAdministrative d
             JOIN etudiant e ON e.idEtudiant = d.idEtudiant
             WHERE d.statut = ? ORDER BY d.dateDemande", [$statut]
        )->fetchAll();
    }

    /** Assigne la demande à un administrateur et change son statut */
    public function traiter(int $idDemande, int $idAdmin, string $statut = self::STATUT_TRAITEE): bool
    {
        return $this->update($idDemande, ['idAdmin' => $idAdmin, 'statut' => $statut]);
    }

    // ----- admin screen -----

    /** @return array{0: string, 1: array} */
    private function whereSearch(array $f): array
    {
        $sql = ' WHERE 1 = 1';
        $p   = [];
        if (($f['q'] ?? '') !== '') {
            $sql .= ' AND (e.nom LIKE ? OR e.prenom LIKE ? OR e.cne LIKE ? OR d.typeDemande LIKE ?)';
            $like = '%' . addcslashes($f['q'], '%_\\') . '%';
            array_push($p, $like, $like, $like, $like);
        }
        if (in_array($f['statut'] ?? '', [self::STATUT_EN_ATTENTE, self::STATUT_TRAITEE, self::STATUT_REFUSEE], true)) {
            $sql .= ' AND d.statut = ?';
            $p[] = $f['statut'];
        }
        return [$sql, $p];
    }

    /** Requests with the student and the admin who handled them; pending first, then newest. */
    public function search(array $filters, int $limit, int $offset = 0): array
    {
        [$where, $params] = $this->whereSearch($filters);
        return $this->query(
            "SELECT d.idDemande, d.typeDemande, d.dateDemande, d.statut,
                    e.cne, e.nom, e.prenom, a.nom AS adminNom, a.prenom AS adminPrenom
             FROM demandeAdministrative d
             JOIN etudiant e ON e.idEtudiant = d.idEtudiant
             LEFT JOIN administrateur a ON a.idAdmin = d.idAdmin
             $where
             ORDER BY (d.statut = 'EN_ATTENTE') DESC, d.dateDemande DESC
             LIMIT " . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        )->fetchAll();
    }

    public function countSearch(array $filters = []): int
    {
        [$where, $params] = $this->whereSearch($filters);
        return (int) $this->query(
            "SELECT COUNT(*) FROM demandeAdministrative d JOIN etudiant e ON e.idEtudiant = d.idEtudiant $where",
            $params
        )->fetchColumn();
    }

    /** The request with the student's account id, to notify them. */
    public function findWithStudent(int $id): ?array
    {
        $row = $this->query(
            'SELECT d.idDemande, d.typeDemande, d.statut, e.idUser, e.nom, e.prenom
             FROM demandeAdministrative d JOIN etudiant e ON e.idEtudiant = d.idEtudiant
             WHERE d.idDemande = ? LIMIT 1',
            [$id]
        )->fetch();
        return $row ?: null;
    }

    /** Decides a pending request. False if it was already decided (or does not exist). */
    public function decide(int $id, ?int $idAdmin, string $statut): bool
    {
        return $this->query(
            'UPDATE demandeAdministrative SET statut = ?, idAdmin = ? WHERE idDemande = ? AND statut = ?',
            [$statut, $idAdmin, $id, self::STATUT_EN_ATTENTE]
        )->rowCount() > 0;
    }

    /** A student's pending request of the same type already exists? */
    public function hasPending(int $idEtudiant, string $type): bool
    {
        return $this->query(
            'SELECT 1 FROM demandeAdministrative WHERE idEtudiant = ? AND typeDemande = ? AND statut = ? LIMIT 1',
            [$idEtudiant, $type, self::STATUT_EN_ATTENTE]
        )->fetchColumn() !== false;
    }
}
