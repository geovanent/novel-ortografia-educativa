
USE novel;

CREATE TABLE Jogador (
email Varchar(254) NOT NULL,
senha Varchar(255) NOT NULL,
experiencia int DEFAULT 0,
tempoTotal int DEFAULT 0,
codJogador int PRIMARY KEY AUTO_INCREMENT,
nivel int DEFAULT 1,
login Varchar(20) NOT NULL,
avatar Varchar(20) NOT NULL,
nome Varchar(50) NOT NULL
);

ALTER TABLE Jogador CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Grafema (
tipoGrafema Varchar(30) NOT NULL,
codGrafema int PRIMARY KEY AUTO_INCREMENT,
quadros int NOT NULL
);

ALTER TABLE Grafema CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Teste (
enunciado Varchar (5000) NOT NULL,
codTeste int PRIMARY KEY AUTO_INCREMENT,
gabarito Varchar(200) NOT NULL,
codGrafema int NOT NULL,
FOREIGN KEY(codGrafema) REFERENCES Grafema (codGrafema)
);

ALTER TABLE Teste CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Alternativas (
codAlternativa int PRIMARY KEY AUTO_INCREMENT,
alternativa Varchar(200) NOT NULL,
codTeste int NOT NULL,
FOREIGN KEY(codTeste) REFERENCES Teste (codTeste)
);

ALTER TABLE Alternativas CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE GrupoTeste (
codGrupo int PRIMARY KEY AUTO_INCREMENT,
grafemas VarChar(30) NOT NULL
);

ALTER TABLE GrupoTeste CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Texto (
textoIncompleto Varchar(15000) NOT NULL,
imagem VarChar(50),
codTexto int PRIMARY KEY AUTO_INCREMENT
);

ALTER TABLE Texto CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Gabarito (
posicao int NOT NULL,
letraGabarito Char(2) NOT NULL,
codGabarito int PRIMARY KEY AUTO_INCREMENT,
codTexto int NOT NULL,
FOREIGN KEY(codTexto) REFERENCES Texto (codTexto)
);

ALTER TABLE Gabarito CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE GrafemaTexto (
codTexto int NOT NULL,
codGrafema int NOT NULL,
FOREIGN KEY(codTexto) REFERENCES Texto (codTexto),
FOREIGN KEY(codGrafema) REFERENCES Grafema (codGrafema)
);

ALTER TABLE GrafemaTexto CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Palavra (
palavraIncompleta Varchar(25) NOT NULL,
letraGabarito Char(5) NOT NULL,
enunciado Varchar(600) NOT NULL,
justificativa VarChar(200) NOT NULL,
imagem VarChar(50),
palavraCompleta VarChar(25) NOT NULL,
codPalavra int PRIMARY KEY AUTO_INCREMENT,
codGrafema int NOT NULL,
FOREIGN KEY(codGrafema) REFERENCES Grafema (codGrafema)
);

ALTER TABLE Palavra CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Rodada (
pontuacao int NOT NULL,
duracao int NOT NULL,
tipoRodada Enum ('palavra','texto', 'teste') NOT NULL,
codRodada int PRIMARY KEY AUTO_INCREMENT,
codJogador int NOT NULL,
FOREIGN KEY(codJogador) REFERENCES Jogador (codJogador)
);

ALTER TABLE Rodada CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE RodadaGrafema (
codGrafema int NOT NULL,
codRodada int NOT NULL,
FOREIGN KEY(codGrafema) REFERENCES Grafema (codGrafema),
FOREIGN KEY(codRodada) REFERENCES Rodada (codRodada)
);

ALTER TABLE RodadaGrafema CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Conquista (
codConquista int PRIMARY KEY AUTO_INCREMENT,
nomeConquista Varchar(200) NOT NULL,
experienciaDesbloqueio int NOT NULL
);

ALTER TABLE Conquista CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE ConquistaJogador (
codJogador int NOT NULL,
codConquista int NOT NULL,
FOREIGN KEY(codJogador) REFERENCES Jogador (codJogador),
FOREIGN KEY(codConquista) REFERENCES Conquista (codConquista)
);

ALTER TABLE ConquistaJogador CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Cenas (
codCena int PRIMARY KEY AUTO_INCREMENT,
nomeCena Varchar(20) NOT NULL,
quadros int NOT NULL,
nivelDesbloqueio int NOT NULL
);

ALTER TABLE Cenas CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Administrador (
login Varchar(20) NOT NULL,
senha Varchar(255) NOT NULL,
nome Varchar(50) NOT NULL,
email Varchar(254) NOT NULL,
codAdministrador int PRIMARY KEY AUTO_INCREMENT
);

ALTER TABLE Administrador CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Bonus (
codBonus int PRIMARY KEY AUTO_INCREMENT,
experienciaNecessaria int NOT NULL,
textoBonus VarChar(225) NOT NULL
);

ALTER TABLE Bonus CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE PalavraBonus (
codPalavraBonus int PRIMARY KEY AUTO_INCREMENT,
palavra VarChar(20) NOT NULL,
inicio int NOT NULL,
fim int NOT NULL,
codBonus int NOT NULL,
FOREIGN KEY(codBonus) REFERENCES Bonus (codBonus)
);

ALTER TABLE PalavraBonus CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE JogadorBonus (
codBonus int,
codJogador int,
FOREIGN KEY(codBonus) REFERENCES Bonus (codBonus),
FOREIGN KEY(codJogador) REFERENCES Jogador (codJogador)
);

ALTER TABLE JogadorBonus CHARACTER SET utf8 COLLATE utf8_general_ci;
