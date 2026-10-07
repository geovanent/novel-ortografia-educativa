UPDATE Palavra SET palavraIncompleta='D_spensa' WHERE codPalavra=101 AND palavraIncompleta='D_pensa' AND palavraCompleta='Dispensa' AND letraGabarito='i';
UPDATE Palavra SET palavraCompleta='Seringa' WHERE codPalavra=103 AND palavraIncompleta='S_ringa' AND palavraCompleta='Seringaa' AND letraGabarito='e';
INSERT IGNORE INTO NovelMigration VALUES(4,NOW());
