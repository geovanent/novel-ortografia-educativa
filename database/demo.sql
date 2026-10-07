-- Contas de demonstração: somente no desenvolvimento local.
INSERT INTO Jogador (nome, email, login, senha, avatar) VALUES
  -- Hash de password_hash(); a senha em claro e "123@Mudar".
  ("Teste", "teste@mail.com", "teste", "$2y$12$KW0f2mGQ0vof9zf.Z8etf.x0KdkDnL9IxMajL82P5qGR.opA7EkiW", "graciliano");


INSERT INTO Administrador (nome, email, login, senha) VALUES
    -- Hash de password_hash(); a senha em claro e "123@Mudar".
    ("Administrador do Novel", "novel.noreply@gmail.com", "teste123", "$2y$12$0JTPZTTZGJ2sklF8oTin.uaU5zBfcOkv1eoYfcym7841He5fCoUNm");
