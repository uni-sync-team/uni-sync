class Etudiant extends BaseModel
{
    protected string $table = 'etudiant';
    protected string $primaryKey = 'idEtudiant';
    protected array $fillable = ['idUser', 'cne', 'nom', 'prenom', 'dateNaissance', 'telephone'];

    public function findByUser(int $idUser): ?array
    {
        return $this->findOneBy('idUser', $idUser);
    }

    public function findByCne(string $cne): ?array
    {
        return $this->findOneBy('cne', $cne);
    }

    public function search(string $term): array
    {
        $like = '%' . $term . '%';
        return $this->query(
            "SELECT * FROM etudiant
             WHERE nom LIKE ? OR prenom LIKE ? OR cne LIKE ?
             ORDER BY nom, prenom LIMIT 50", [$like, $like, $like]
        )->fetchAll();
    }

    public function getInscriptions(int $idEtudiant): array
    {
        return $this->query(
            "SELECT i.*, f.intitule AS formation, a.libelle AS annee
             FROM inscription i
             JOIN formation f ON f.idFormation = i.idFormation
             JOIN anneeUniversitaire a ON a.idAnnee = i.idAnnee
             WHERE i.idEtudiant = ?
             ORDER BY a.dateDebut DESC", [$idEtudiant]
        )->fetchAll();
    }

    public function getNotes(int $idEtudiant): array
    {
        return $this->query(
            "SELECT n.*, e.type, e.dateEvaluation, e.bareme, m.code, m.intitule AS module
             FROM note n
             JOIN evaluation e ON e.idEvaluation = n.idEvaluation
             JOIN module m ON m.idModule = e.idModule
             WHERE n.idEtudiant = ?
             ORDER BY e.dateEvaluation DESC", [$idEtudiant]
        )->fetchAll();
    }

    public function getResultats(int $idEtudiant): array
    {
        return $this->query(
            "SELECT r.*, s.libelle AS semestre
             FROM resultatSemestre r
             JOIN semestre s ON s.idSemestre = r.idSemestre
             WHERE r.idEtudiant = ?
             ORDER BY s.numero", [$idEtudiant]
        )->fetchAll();
    }

    public function getDemandes(int $idEtudiant): array
    {
        return $this->query(
            "SELECT * FROM demandeAdministrative WHERE idEtudiant = ? ORDER BY dateDemande DESC", [$idEtudiant]
        )->fetchAll();
    }

    public function getReclamations(int $idEtudiant): array
    {
        return $this->query(
            "SELECT rn.*, e.type, m.intitule AS module
             FROM reclamationNote rn
             JOIN evaluation e ON e.idEvaluation = rn.idEvaluation
             JOIN module m ON m.idModule = e.idModule
             WHERE rn.idEtudiant = ?", [$idEtudiant]
        )->fetchAll();
    }
}
