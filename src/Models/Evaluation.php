<?php
namespace App\Models;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use PDOStatement;

class Evaluation
{
    protected string $table = 'evaluation';
    protected string $primaryKey = 'idEvaluation';
    protected array $fillable = ['idModule', 'type', 'dateEvaluation', 'bareme', 'coefficient'];

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

    public function findByModule(int $idModule): array
    {
        return $this->query(
            "SELECT * FROM evaluation WHERE idModule = ? ORDER BY dateEvaluation", [$idModule]
        )->fetchAll();
    }

    public function getNotes(int $idEvaluation, ?string $session = null): array
    {
        $sql = "SELECT n.*, e.cne, e.nom, e.prenom
                FROM note n
                JOIN etudiant e ON e.idEtudiant = n.idEtudiant
                WHERE n.idEvaluation = ?";
        $params = [$idEvaluation];
        if ($session !== null) {
            $sql .= " AND n.session = ?";
            $params[] = $session;
        }
        return $this->query($sql . " ORDER BY e.nom, e.prenom", $params)->fetchAll();
    }

    /** Statistiques de l'évaluation : moyenne, min, max, nombre de notes */
    public function getStats(int $idEvaluation): array
    {
        return $this->query(
            "SELECT COUNT(valeur) AS nbNotes, AVG(valeur) AS moyenne,
                    MIN(valeur) AS minimum, MAX(valeur) AS maximum
             FROM note WHERE idEvaluation = ?", [$idEvaluation]
        )->fetch();
    }

    public const TYPES = ['Contrôle continu', 'TP', 'Examen final', 'Rattrapage'];

    private const DETAIL_SELECT = "SELECT ev.*, m.code AS moduleCode, m.intitule AS module,
                                          m.idSemestre, s.idFormation, f.intitule AS formation,
                                          (SELECT COUNT(*) FROM note n WHERE n.idEvaluation = ev.idEvaluation) AS nbNotes
                                   FROM evaluation ev
                                   JOIN module m    ON m.idModule    = ev.idModule
                                   JOIN semestre s  ON s.idSemestre  = m.idSemestre
                                   JOIN formation f ON f.idFormation = s.idFormation";

    /** Evaluations with module / formation labels, newest first. Optional filters: module, type. */
    public function findAllDetailed(int $idModule = 0, string $type = ''): array
    {
        $sql = self::DETAIL_SELECT . " WHERE 1 = 1";
        $params = [];
        if ($idModule > 0) {
            $sql .= " AND ev.idModule = ?";
            $params[] = $idModule;
        }
        if ($type !== '') {
            $sql .= " AND ev.type = ?";
            $params[] = $type;
        }
        return $this->query($sql . " ORDER BY ev.dateEvaluation DESC, ev.idEvaluation DESC", $params)->fetchAll();
    }

    public function findDetailed(int $id): ?array
    {
        $row = $this->query(self::DETAIL_SELECT . " WHERE ev.idEvaluation = ?", [$id])->fetch();
        return $row ?: null;
    }

    /**
     * Grading sheet: every student validly enrolled in the evaluation's formation,
     * with their current grade (if any) for the given session.
     */
    public function getFeuilleNotes(int $idEvaluation, string $session = 'NORMALE'): array
    {
        return $this->query(
            "SELECT DISTINCT e.idEtudiant, e.cne, e.nom, e.prenom, n.valeur
             FROM evaluation ev
             JOIN module m      ON m.idModule   = ev.idModule
             JOIN semestre s    ON s.idSemestre = m.idSemestre
             JOIN inscription i ON i.idFormation = s.idFormation AND i.statut = 'VALIDEE'
             JOIN etudiant e    ON e.idEtudiant = i.idEtudiant
             LEFT JOIN note n   ON n.idEvaluation = ev.idEvaluation
                               AND n.idEtudiant = e.idEtudiant AND n.session = ?
             WHERE ev.idEvaluation = ?
             ORDER BY e.nom, e.prenom",
            [$session, $idEvaluation]
        )->fetchAll();
    }
}
