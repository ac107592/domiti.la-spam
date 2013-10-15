
DROP TABLE IF EXISTS Dominios;

CREATE TABLE Dominios (
	pkDominios INTEGER PRIMARY KEY AUTOINCREMENT,
	nome TEXT NOT NULL
);



DROP TABLE IF EXISTS Contatos;

CREATE TABLE Contatos (
	pkContatos INTEGER PRIMARY KEY AUTOINCREMENT,
	fkDominios INTEGER NOT NULL,
	usuario TEXT NOT NULL,

	UNIQUE(fkDominios, usuario),


	FOREIGN KEY(fkDominios)
		REFERENCES Dominios(pkDominios) ON DELETE CASCADE ON UPDATE CASCADE
);

DROP VIEW IF EXISTS LstDominios;

CREATE VIEW LstDominios AS SELECT COUNT (1) AS total, pkDominios AS id, nome
FROM Contatos, Dominios
WHERE (fkDominios = pkDominios)
GROUP BY fkDominios;