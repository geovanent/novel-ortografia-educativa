-- O segundo quadro da cena 16 já existe para os quatro avatares.
UPDATE Cenas SET quadros=2 WHERE nomeCena='cena16';
INSERT IGNORE INTO NovelMigration VALUES(2,NOW());
