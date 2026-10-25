CREATE TABLE anneeUniversitaire (
  idAnnee    INT AUTO_INCREMENT PRIMARY KEY,
  libelle    VARCHAR(50) NOT NULL,
  dateDebut  DATE NOT NULL,
  dateFin    DATE NOT NULL
) ENGINE=InnoDB;
 
CREATE TABLE utilisateur (
  idUser       INT AUTO_INCREMENT PRIMARY KEY,
  login        VARCHAR(50)  NOT NULL UNIQUE,
  email        VARCHAR(150) NOT NULL UNIQUE,
  motDePasse   VARCHAR(255) NOT NULL,
  actif        BOOLEAN NOT NULL DEFAULT TRUE,
  dateCreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
 
CREATE TABLE role (
  idRole      INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255)
) ENGINE=InnoDB;
 
CREATE TABLE departement (
  idDepartement INT AUTO_INCREMENT PRIMARY KEY,
  nom           VARCHAR(100) NOT NULL,
  description   VARCHAR(255)
) ENGINE=InnoDB;
 
CREATE TABLE salle (
  idSalle   INT AUTO_INCREMENT PRIMARY KEY,
  nom       VARCHAR(50) NOT NULL,
  capacite  INT NOT NULL,
  typeSalle VARCHAR(50)
) ENGINE=InnoDB;
 
-- ========== Utilisateurs et rôles ==========
 
CREATE TABLE userRole (
  idUser INT NOT NULL,
  idRole INT NOT NULL,
  PRIMARY KEY (idUser, idRole),
  FOREIGN KEY (idUser) REFERENCES utilisateur(idUser) ON DELETE CASCADE,
  FOREIGN KEY (idRole) REFERENCES role(idRole) ON DELETE CASCADE
) ENGINE=InnoDB;
 
CREATE TABLE administrateur (
  idAdmin INT AUTO_INCREMENT PRIMARY KEY,
  idUser  INT NOT NULL UNIQUE,
  nom     VARCHAR(100) NOT NULL,
  prenom  VARCHAR(100) NOT NULL,
  service VARCHAR(100),
  FOREIGN KEY (idUser) REFERENCES utilisateur(idUser)
) ENGINE=InnoDB;
 
CREATE TABLE enseignant (
  idEnseignant  INT AUTO_INCREMENT PRIMARY KEY,
  idDepartement INT NOT NULL,
  idUser        INT NOT NULL UNIQUE,
  nom           VARCHAR(100) NOT NULL,
  prenom        VARCHAR(100) NOT NULL,
  grade         VARCHAR(50),
  FOREIGN KEY (idDepartement) REFERENCES departement(idDepartement),
  FOREIGN KEY (idUser) REFERENCES utilisateur(idUser)
) ENGINE=InnoDB;
 
CREATE TABLE etudiant (
  idEtudiant    INT AUTO_INCREMENT PRIMARY KEY,
  idUser        INT NOT NULL UNIQUE,
  cne           VARCHAR(20) NOT NULL UNIQUE,
  nom           VARCHAR(100) NOT NULL,
  prenom        VARCHAR(100) NOT NULL,
  dateNaissance DATE,
  telephone     VARCHAR(20),
  FOREIGN KEY (idUser) REFERENCES utilisateur(idUser)
) ENGINE=InnoDB;
 
-- ========== Structure pédagogique ==========
 
CREATE TABLE filiere (
  idFiliere     INT AUTO_INCREMENT PRIMARY KEY,
  idDepartement INT NOT NULL,
  nom           VARCHAR(100) NOT NULL,
  description   VARCHAR(255),
  FOREIGN KEY (idDepartement) REFERENCES departement(idDepartement)
) ENGINE=InnoDB;
 
CREATE TABLE formation (
  idFormation INT AUTO_INCREMENT PRIMARY KEY,
  idFiliere   INT NOT NULL,
  intitule    VARCHAR(150) NOT NULL,
  niveau      VARCHAR(50),
  duree       INT COMMENT 'Durée en années',
  FOREIGN KEY (idFiliere) REFERENCES filiere(idFiliere)
) ENGINE=InnoDB;
 
CREATE TABLE semestre (
  idSemestre  INT AUTO_INCREMENT PRIMARY KEY,
  idFormation INT NOT NULL,
  libelle     VARCHAR(50) NOT NULL,
  numero      INT NOT NULL,
  FOREIGN KEY (idFormation) REFERENCES formation(idFormation)
) ENGINE=InnoDB;
 
CREATE TABLE module (
  idModule      INT AUTO_INCREMENT PRIMARY KEY,
  idSemestre    INT NOT NULL,
  code          VARCHAR(20) NOT NULL UNIQUE,
  intitule      VARCHAR(150) NOT NULL,
  volumeHoraire INT,
  coefficient   DECIMAL(4,2) NOT NULL DEFAULT 1,
  FOREIGN KEY (idSemestre) REFERENCES semestre(idSemestre)
) ENGINE=InnoDB;
 
-- ========== Inscriptions ==========
 
CREATE TABLE inscription (
  idInscription   INT AUTO_INCREMENT PRIMARY KEY,
  idEtudiant      INT NOT NULL,
  idFormation     INT NOT NULL,
  idAnnee         INT NOT NULL,
  dateInscription DATE NOT NULL,
  statut          VARCHAR(30) NOT NULL DEFAULT 'EN_ATTENTE',
  UNIQUE (idEtudiant, idFormation, idAnnee),
  FOREIGN KEY (idEtudiant)  REFERENCES etudiant(idEtudiant),
  FOREIGN KEY (idFormation) REFERENCES formation(idFormation),
  FOREIGN KEY (idAnnee)     REFERENCES anneeUniversitaire(idAnnee)
) ENGINE=InnoDB;
 
CREATE TABLE inscriptionModule (
  idInscription INT NOT NULL,
  idModule      INT NOT NULL,
  statut        VARCHAR(30),
  PRIMARY KEY (idInscription, idModule),
  FOREIGN KEY (idInscription) REFERENCES inscription(idInscription) ON DELETE CASCADE,
  FOREIGN KEY (idModule)      REFERENCES module(idModule)
) ENGINE=InnoDB;
 
-- ========== Évaluations et résultats ==========
 
CREATE TABLE evaluation (
  idEvaluation   INT AUTO_INCREMENT PRIMARY KEY,
  idModule       INT NOT NULL,
  type           VARCHAR(50) NOT NULL,
  dateEvaluation DATE,
  bareme         DECIMAL(5,2) NOT NULL DEFAULT 20,
  coefficient    DECIMAL(4,2) NOT NULL DEFAULT 1,
  FOREIGN KEY (idModule) REFERENCES module(idModule)
) ENGINE=InnoDB;
 
CREATE TABLE note (
  idNote       INT AUTO_INCREMENT PRIMARY KEY,
  idEtudiant   INT NOT NULL,
  idEvaluation INT NOT NULL,
  valeur       DECIMAL(5,2),
  session      VARCHAR(30),
  UNIQUE (idEtudiant, idEvaluation, session),
  FOREIGN KEY (idEtudiant)   REFERENCES etudiant(idEtudiant),
  FOREIGN KEY (idEvaluation) REFERENCES evaluation(idEvaluation)
) ENGINE=InnoDB;
 
CREATE TABLE resultatSemestre (
  idResultat INT AUTO_INCREMENT PRIMARY KEY,
  idEtudiant INT NOT NULL,
  idSemestre INT NOT NULL,
  moyenne    DECIMAL(5,2),
  decision   VARCHAR(30),
  UNIQUE (idEtudiant, idSemestre),
  FOREIGN KEY (idEtudiant) REFERENCES etudiant(idEtudiant),
  FOREIGN KEY (idSemestre) REFERENCES semestre(idSemestre)
) ENGINE=InnoDB;
 
CREATE TABLE reclamationNote (
  idReclamation INT AUTO_INCREMENT PRIMARY KEY,
  idEtudiant    INT NOT NULL,
  idEvaluation  INT NOT NULL,
  motif         TEXT,
  statut        VARCHAR(30) NOT NULL DEFAULT 'EN_ATTENTE',
  FOREIGN KEY (idEtudiant)   REFERENCES etudiant(idEtudiant),
  FOREIGN KEY (idEvaluation) REFERENCES evaluation(idEvaluation)
) ENGINE=InnoDB;
 
-- ========== Emploi du temps ==========
 
CREATE TABLE seance (
  idSeance     INT AUTO_INCREMENT PRIMARY KEY,
  idModule     INT NOT NULL,
  idEnseignant INT NOT NULL,
  idSalle      INT NOT NULL,
  dateDebut    DATETIME NOT NULL,
  dateFin      DATETIME NOT NULL,
  typeSeance   VARCHAR(30),
  FOREIGN KEY (idModule)     REFERENCES module(idModule),
  FOREIGN KEY (idEnseignant) REFERENCES enseignant(idEnseignant),
  FOREIGN KEY (idSalle)      REFERENCES salle(idSalle)
) ENGINE=InnoDB;
 
-- ========== Administratif et suivi ==========
 
CREATE TABLE demandeAdministrative (
  idDemande    INT AUTO_INCREMENT PRIMARY KEY,
  idEtudiant   INT NOT NULL,
  idAdmin      INT,
  typeDemande  VARCHAR(100) NOT NULL,
  dateDemande  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  statut       VARCHAR(30) NOT NULL DEFAULT 'EN_ATTENTE',
  FOREIGN KEY (idEtudiant) REFERENCES etudiant(idEtudiant),
  FOREIGN KEY (idAdmin)    REFERENCES administrateur(idAdmin)
) ENGINE=InnoDB;
 
CREATE TABLE notification (
  idNotification INT AUTO_INCREMENT PRIMARY KEY,
  idUser         INT NOT NULL,
  message        TEXT NOT NULL,
  dateCreation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lu             BOOLEAN NOT NULL DEFAULT FALSE,
  FOREIGN KEY (idUser) REFERENCES utilisateur(idUser) ON DELETE CASCADE
) ENGINE=InnoDB;
 
CREATE TABLE historiqueAction (
  idAction   INT AUTO_INCREMENT PRIMARY KEY,
  idUser     INT NOT NULL,
  action     VARCHAR(255) NOT NULL,
  dateAction DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idUser) REFERENCES utilisateur(idUser)
) ENGINE=InnoDB;


CREATE TABLE publication (
  idPublication   INT AUTO_INCREMENT PRIMARY KEY,
  idAdmin         INT NOT NULL,
  titre           VARCHAR(150) NOT NULL,
  contenu         TEXT NOT NULL,
  datePublication DATE NOT NULL DEFAULT (CURRENT_DATE),
  dateExpiration  DATE NULL,
  actif           BOOLEAN NOT NULL DEFAULT TRUE,
  FOREIGN KEY (idAdmin) REFERENCES administrateur(idAdmin)
) ENGINE=InnoDB;
