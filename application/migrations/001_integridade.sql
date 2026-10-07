-- Aplicar pelo comando CLI: php index.php manutencao migrar
CREATE TABLE IF NOT EXISTS NovelMigration (version INT PRIMARY KEY, appliedAt DATETIME NOT NULL);
CREATE TABLE IF NOT EXISTS Tentativa (
 token CHAR(64) PRIMARY KEY, codJogador INT NOT NULL, modo VARCHAR(10) NOT NULL,
 habilidade VARCHAR(255) NOT NULL, snapshot MEDIUMTEXT NOT NULL, inicio BIGINT NOT NULL,
 fim BIGINT NULL, codRodada INT NULL, resultado MEDIUMTEXT NULL,
 INDEX jogador_inicio (codJogador, inicio), FOREIGN KEY (codJogador) REFERENCES Jogador(codJogador)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Resposta (
 codRodada INT NOT NULL, posicao INT NOT NULL, codGrafema INT NOT NULL,
 questao TEXT NOT NULL, resposta TEXT NOT NULL, correta TEXT NOT NULL, acertou TINYINT NOT NULL,
 PRIMARY KEY (codRodada,posicao), FOREIGN KEY (codRodada) REFERENCES Rodada(codRodada)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Dominio (
 codJogador INT NOT NULL, modo VARCHAR(10) NOT NULL, habilidade VARCHAR(255) NOT NULL,
 PRIMARY KEY(codJogador,modo,habilidade)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS CenaLida (codJogador INT NOT NULL, nomeCena VARCHAR(20) NOT NULL,
 PRIMARY KEY(codJogador,nomeCena)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Recuperacao (
 tokenHash CHAR(64) PRIMARY KEY, codJogador INT NOT NULL, expira BIGINT NOT NULL, usado TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS LimiteAcesso (
 chave CHAR(64) NOT NULL, janela BIGINT NOT NULL, quantidade INT NOT NULL DEFAULT 0,
 PRIMARY KEY(chave,janela)
) ENGINE=InnoDB;
ALTER TABLE Jogador MODIFY email VARCHAR(254) NOT NULL;
ALTER TABLE Administrador MODIFY email VARCHAR(254) NOT NULL;
INSERT IGNORE INTO NovelMigration VALUES(1,NOW());
