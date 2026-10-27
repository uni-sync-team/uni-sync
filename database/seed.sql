SET NAMES utf8mb4;


INSERT INTO role (nom, description) VALUES
  ('etudiant', 'Etudiant inscrit'),
  ('prof',     'Enseignant'),
  ('admin',    'Administration');

INSERT INTO utilisateur (login, email, motDePasse) VALUES
  ('etudiant', 'etud@test.com',  '$2y$10$dZushs6DjJjcbTM7AjkCjOtXEDxqggIR0iZJpRO3MlEEjq6wkOnbu'),
  ('prof',     'prof@test.com',  '$2y$10$dZushs6DjJjcbTM7AjkCjOtXEDxqggIR0iZJpRO3MlEEjq6wkOnbu'),
  ('admin',    'admin@test.com', '$2y$10$dZushs6DjJjcbTM7AjkCjOtXEDxqggIR0iZJpRO3MlEEjq6wkOnbu');


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
SELECT (SELECT idDepartement FROM departement WHERE nom = 'Informatique'), idUser, 'Test', 'Prof', 'Professeur'
FROM utilisateur WHERE login = 'prof';

INSERT INTO etudiant (idUser, cne, nom, prenom)
SELECT idUser, 'CNE0000001', 'Test', 'Etudiant' FROM utilisateur WHERE login = 'etudiant';
