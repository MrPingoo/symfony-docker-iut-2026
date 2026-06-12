-- Exécuté automatiquement au PREMIER démarrage de MariaDB (base vide).
-- Autorise l'utilisateur applicatif à créer/utiliser la base de TEST
-- (app_test), nécessaire pour lancer PHPUnit avec une base isolée.
CREATE DATABASE IF NOT EXISTS `app_test`;
GRANT ALL PRIVILEGES ON `app\_test`.* TO 'app'@'%';
FLUSH PRIVILEGES;
