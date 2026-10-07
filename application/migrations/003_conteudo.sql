-- Corrigir somente registros ainda iguais ao conteúdo legado, sem substituir edições posteriores.
ALTER TABLE Teste MODIFY gabarito VARCHAR(200) NOT NULL;
UPDATE Teste SET gabarito='Gimnosperma' WHERE codTeste=13 AND gabarito LIKE '%Gimnosperma' AND EXISTS (SELECT 1 FROM Alternativas a WHERE a.codTeste=13 AND a.alternativa='Gimnosperma');
UPDATE Teste SET gabarito='chocalho, cheio, seixos.' WHERE codTeste=24 AND gabarito='chocalho, cheio, seixos' AND EXISTS (SELECT 1 FROM Alternativas a WHERE a.codTeste=24 AND a.alternativa='chocalho, cheio, seixos.');
UPDATE Alternativas SET alternativa='lo_tra, a_fíbio' WHERE codTeste=60 AND alternativa='lo_tra, a¬_fíbio';
UPDATE Alternativas SET alternativa='lâ_pada, u_bigo' WHERE codTeste=60 AND alternativa='lâ¬_pada, u_bigo';
INSERT IGNORE INTO NovelMigration VALUES(3,NOW());
