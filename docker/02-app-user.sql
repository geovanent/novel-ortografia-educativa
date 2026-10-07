-- Apenas desenvolvimento. No Coolify, configure o usuário pelo serviço de banco.
CREATE USER IF NOT EXISTS 'novel_app'@'%' IDENTIFIED BY 'novel-local-development';
GRANT SELECT, INSERT, UPDATE, DELETE ON novel.* TO 'novel_app'@'%';
