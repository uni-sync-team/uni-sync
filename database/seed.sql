SET NAMES utf8mb4;


INSERT INTO role (nom, description) VALUES
  ('etudiant', 'Etudiant inscrit'),
  ('prof',     'Enseignant'),
  ('admin',    'Administration');

INSERT INTO utilisateur (login, email, motDePasse) VALUES
  ('etudiant', 'etud@test.com',  '$2y$10$ve91ysUlxWUobz4xl8kcAuI4.5W9mG9GeKPCH.dzhTfKk9BQ95Ase'),
  ('prof',     'prof@test.com',  '$2y$10$ve91ysUlxWUobz4xl8kcAuI4.5W9mG9GeKPCH.dzhTfKk9BQ95Ase'),
  ('admin',    'admin@test.com', '$2y$10$ve91ysUlxWUobz4xl8kcAuI4.5W9mG9GeKPCH.dzhTfKk9BQ95Ase');

INSERT INTO userRole (idUser, idRole)
SELECT u.idUser, r.idRole
FROM utilisateur u
JOIN role r ON r.nom = u.login;

-- Profil administration du compte admin (requis par publication.idAdmin)
INSERT INTO administrateur (idUser, nom, prenom, service)
SELECT idUser, 'Admin', 'Principal', 'Scolarité'
FROM utilisateur
WHERE login = 'admin';

-- Annonces d'exemple pour la page d'accueil (dates relatives à aujourd'hui)
INSERT INTO publication (idAdmin, titre, contenu, datePublication)
SELECT a.idAdmin, p.titre, p.contenu, CURRENT_DATE - INTERVAL p.jours DAY
FROM administrateur a
JOIN (
  SELECT 'Notes du semestre 3 publiées' AS titre,
         'Connectez-vous pour consulter vos notes par module et par évaluation.' AS contenu, 1 AS jours
  UNION ALL SELECT 'Réclamations ouvertes',
         'Vous pouvez contester une note en indiquant le motif. Suivez le statut dans votre espace.', 3
  UNION ALL SELECT 'Calendrier des examens',
         'Les séances et les salles sont publiées par l’administration du département.', 6
  UNION ALL SELECT 'Inscriptions aux modules',
         'Vérifiez votre inscription à la formation et à vos modules pour l’année universitaire.', 9
) p
WHERE a.nom = 'Admin';

-- ===== Catalogue d'exemple : départements, filières, formations, modules =====
INSERT INTO anneeUniversitaire (libelle, dateDebut, dateFin) VALUES ('2026-2027', '2026-09-01', '2027-07-31');

INSERT INTO departement (nom, description) VALUES
  ('Informatique',   'Génie logiciel, intelligence artificielle, réseaux et systèmes.'),
  ('Mathématiques',  'Mathématiques appliquées, statistique et analyse de données.'),
  ('Physique',       'Physique fondamentale, électronique et automatique.');

INSERT INTO filiere (idDepartement, nom, description) VALUES
  ((SELECT idDepartement FROM departement WHERE nom = 'Informatique'),  'Génie Informatique',         'Conception et développement de logiciels et de systèmes d’information.'),
  ((SELECT idDepartement FROM departement WHERE nom = 'Informatique'),  'Intelligence Artificielle',   'Apprentissage automatique, vision par ordinateur et traitement du langage.'),
  ((SELECT idDepartement FROM departement WHERE nom = 'Mathématiques'), 'Mathématiques Appliquées',    'Modélisation, optimisation et calcul scientifique.'),
  ((SELECT idDepartement FROM departement WHERE nom = 'Mathématiques'), 'Statistique et Data Science', 'Statistique, probabilités et science des données.'),
  ((SELECT idDepartement FROM departement WHERE nom = 'Physique'),      'Électronique et Automatique', 'Systèmes électroniques, capteurs et commande.');

INSERT INTO formation (idFiliere, intitule, niveau, duree) VALUES
  ((SELECT idFiliere FROM filiere WHERE nom = 'Génie Informatique'),         'Licence en Génie Informatique',   'Licence', 3),
  ((SELECT idFiliere FROM filiere WHERE nom = 'Génie Informatique'),         'Master en Génie Logiciel',        'Master',  2),
  ((SELECT idFiliere FROM filiere WHERE nom = 'Intelligence Artificielle'),  'Licence en Intelligence Artificielle', 'Licence', 3),
  ((SELECT idFiliere FROM filiere WHERE nom = 'Intelligence Artificielle'),  'Master en Intelligence Artificielle',  'Master',  2),
  ((SELECT idFiliere FROM filiere WHERE nom = 'Mathématiques Appliquées'),   'Licence en Mathématiques Appliquées', 'Licence', 3),
  ((SELECT idFiliere FROM filiere WHERE nom = 'Statistique et Data Science'),'Master en Data Science',          'Master',  2),
  ((SELECT idFiliere FROM filiere WHERE nom = 'Électronique et Automatique'),'Licence en Électronique',         'Licence', 3);

INSERT INTO semestre (idFormation, libelle, numero) VALUES
  ((SELECT idFormation FROM formation WHERE intitule = 'Licence en Génie Informatique'),      'Semestre 1', 1),
  ((SELECT idFormation FROM formation WHERE intitule = 'Licence en Intelligence Artificielle'),'Semestre 1', 1),
  ((SELECT idFormation FROM formation WHERE intitule = 'Licence en Mathématiques Appliquées'), 'Semestre 1', 1);

INSERT INTO module (idSemestre, code, intitule, volumeHoraire, coefficient) VALUES
  (1, 'INF101', 'Algorithmique et programmation', 60, 2),
  (1, 'INF102', 'Bases de données',               50, 2),
  (2, 'IA101',  'Introduction à l’IA',            50, 2),
  (2, 'IA102',  'Python pour la data',            40, 1.5),
  (3, 'MAT101', 'Analyse',                        60, 2),
  (3, 'MAT102', 'Algèbre linéaire',               50, 2);

-- Profils étudiant et enseignant pour les comptes de test
INSERT INTO enseignant (idDepartement, idUser, nom, prenom, grade)
SELECT (SELECT idDepartement FROM departement WHERE nom = 'Informatique'), idUser, 'Test', 'Prof', 'Professeur Assistant'
FROM utilisateur WHERE login = 'prof';

INSERT INTO etudiant (idUser, cne, nom, prenom, telephone, dateNaissance)
SELECT idUser, 'CNE0000001', 'Test', 'Etudiant', '0623974921', CURRENT_DATE FROM utilisateur WHERE login = 'etudiant';

-- Notifications d'exemple
INSERT INTO notification (idUser, message, lu)
SELECT idUser, 'Bienvenue sur UniSync. Votre compte est prêt.', IF(login = 'etudiant', 1, 0)
FROM utilisateur;

INSERT INTO salle (nom, capacite, typeSalle) VALUES
  ('Salle 101',   40,  'Salle de cours'),
  ('Amphi A',     200, 'Amphithéâtre');


INSERT INTO note (idEtudiant, idEvaluation, valeur, session)
SELECT e.idEtudiant, ev.idEvaluation, 11.50, 'NORMALE'
FROM etudiant e
JOIN module m      ON m.code = 'INF102'
JOIN evaluation ev ON ev.idModule = m.idModule AND ev.type = 'Examen final'
WHERE e.cne = 'CNE_T002'
ON DUPLICATE KEY UPDATE valeur = VALUES(valeur);


INSERT INTO note (idEtudiant, idEvaluation, valeur, session)
SELECT e.idEtudiant, ev.idEvaluation, 13.00, 'RATTRAPAGE'
FROM etudiant e
JOIN module m      ON m.code = 'INF102'
JOIN evaluation ev ON ev.idModule = m.idModule AND ev.type = 'Examen final'
WHERE e.cne = 'CNE_T001'
ON DUPLICATE KEY UPDATE valeur = VALUES(valeur);


INSERT INTO note (idEtudiant, idEvaluation, valeur, session)
SELECT e.idEtudiant, ev.idEvaluation, v.valeur, 'NORMALE'
FROM (
  SELECT 'INF101' AS code, 'Contrôle continu' AS type, 16.00 AS valeur UNION ALL
  SELECT 'INF101', 'Examen final',     15.00 UNION ALL
  SELECT 'INF102', 'Examen final',     13.50
) v
JOIN etudiant e    ON e.cne = 'CNE0000001'
JOIN module m      ON m.code = v.code
JOIN evaluation ev ON ev.idModule = m.idModule AND ev.type = v.type
ON DUPLICATE KEY UPDATE valeur = VALUES(valeur);


INSERT INTO resultatSemestre (idEtudiant, idSemestre, moyenne, decision)
SELECT e.idEtudiant,
       (SELECT s.idSemestre FROM semestre s
         JOIN formation f ON f.idFormation = s.idFormation
        WHERE f.intitule = 'Licence en Génie Informatique' AND s.numero = 1),
       v.moyenne, v.decision
FROM (
  SELECT 'CNE_T001' AS cne, 10.83 AS moyenne, 'VALIDE'     AS decision UNION ALL
  SELECT 'CNE_T002',         9.58,            'NON_VALIDE'             UNION ALL
  SELECT 'CNE0000001',      14.42,            'VALIDE'
) v
JOIN etudiant e ON e.cne = v.cne
ON DUPLICATE KEY UPDATE moyenne = VALUES(moyenne), decision = VALUES(decision);

INSERT INTO seance (idModule, idEnseignant, idSalle, dateDebut, dateFin, typeSeance)
SELECT m.idModule,
       (SELECT idEnseignant FROM enseignant WHERE nom = 'Test' AND prenom = 'Prof' LIMIT 1),
       (SELECT idSalle FROM salle WHERE nom = v.salle LIMIT 1),
       v.debut, v.fin, v.type
FROM (
  SELECT 'INF101' AS code, 'Amphi A'   AS salle, '2026-11-09 08:30:00' AS debut, '2026-11-09 10:30:00' AS fin, 'Cours' AS type UNION ALL
  SELECT 'INF101',         'Salle 101',          '2026-11-09 10:45:00',          '2026-11-09 12:45:00',        'TD'            UNION ALL
  SELECT 'INF102',         'Amphi A',            '2026-11-10 08:30:00',          '2026-11-10 10:30:00',        'Cours'         UNION ALL
  SELECT 'INF102',         'Salle 101',          '2026-11-10 14:00:00',          '2026-11-10 16:00:00',        'TP'            UNION ALL
  SELECT 'IA101',          'Amphi A',            '2026-11-11 09:00:00',          '2026-11-11 11:00:00',        'Cours'         UNION ALL
  SELECT 'IA101',          'Salle 101',          '2026-11-12 10:00:00',          '2026-11-12 12:00:00',        'TD'            UNION ALL
  SELECT 'MAT101',         'Amphi A',            '2026-11-13 08:30:00',          '2026-11-13 10:30:00',        'Cours'
) v
JOIN module m ON m.code = v.code;

INSERT INTO evaluation (idModule, type, dateEvaluation, bareme, coefficient)
SELECT m.idModule, v.type, v.date, v.bareme, v.coef
FROM (
  SELECT 'IA101'  AS code, 'Contrôle continu' AS type, '2026-11-16' AS date, 20 AS bareme, 1 AS coef UNION ALL
  SELECT 'IA101',          'Examen final',            '2027-01-18',         20,            2        UNION ALL
  SELECT 'IA102',          'TP',                      '2026-12-02',         20,            1        UNION ALL
  SELECT 'IA102',          'Examen final',            '2027-01-20',         20,            2        UNION ALL
  SELECT 'MAT101',         'Contrôle continu',        '2026-11-20',         20,            1        UNION ALL
  SELECT 'MAT101',         'Examen final',            '2027-01-22',         20,            2        UNION ALL
  SELECT 'MAT102',         'Contrôle continu',        '2026-11-25',         10,            1        UNION ALL
  SELECT 'MAT102',         'Examen final',            '2027-01-25',         20,            2        UNION ALL
  SELECT 'INF101',         'Rattrapage',              '2027-02-15',         20,            2        UNION ALL
  SELECT 'INF102',         'TP',                      '2026-12-05',         20,            1
) v
JOIN module m ON m.code = v.code;