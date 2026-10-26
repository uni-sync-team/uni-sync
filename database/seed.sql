
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
