CREATE TABLE comptes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_compte VARCHAR(10) NOT NULL UNIQUE,
    libelle_compte VARCHAR(100) NOT NULL,
    type_compte ENUM('actif', 'passif') NOT NULL
);

CREATE TABLE operations_comptables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_operation DATE NOT NULL,
    compte_debit VARCHAR(10) NOT NULL,
    compte_credit VARCHAR(10) NOT NULL,
    montant_debit DECIMAL(15, 2) DEFAULT 0,
    montant_credit DECIMAL(15, 2) DEFAULT 0,
    description VARCHAR(255),
    FOREIGN KEY (compte_debit) REFERENCES comptes(numero_compte),
    FOREIGN KEY (compte_credit) REFERENCES comptes(numero_compte)
);


CREATE TABLE journal_comptable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_journal DATE NOT NULL,
    description VARCHAR(255),
    numero_piece VARCHAR(50) NOT NULL,
    montant DECIMAL(15, 2) NOT NULL
);


CREATE TABLE grand_livre (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_mouvement DATE NOT NULL,
    numero_compte VARCHAR(10) NOT NULL,
    debit DECIMAL(15, 2) DEFAULT 0,
    credit DECIMAL(15, 2) DEFAULT 0,
    description VARCHAR(255),
    FOREIGN KEY (numero_compte) REFERENCES comptes(numero_compte)
);


CREATE TABLE bilan_comptable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_bilan DATE NOT NULL,
    numero_compte VARCHAR(10) NOT NULL,
    solde DECIMAL(15, 2) NOT NULL,
    type_compte ENUM('actif', 'passif') NOT NULL,
    FOREIGN KEY (numero_compte) REFERENCES comptes(numero_compte)
);
