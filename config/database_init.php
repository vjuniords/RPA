<?php

function initializeDatabase() {
    try {
        // Primeiro conecta sem selecionar o banco de dados
        $pdo = new PDO(
            "mysql:host=localhost",
            "root",
            ""
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Cria o banco de dados se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS rpa_system");
        
        // Seleciona o banco de dados
        $pdo->exec("USE rpa_system");

        // Cria as tabelas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS prestadores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cpf VARCHAR(14) NOT NULL,
                pis VARCHAR(11),
                endereco TEXT,
                cidade VARCHAR(100),
                estado CHAR(2),
                cep VARCHAR(9),
                telefone VARCHAR(15),
                email VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rpas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                prestador_id INT,
                numero_rpa VARCHAR(20) NOT NULL,
                data_emissao DATE NOT NULL,
                valor_bruto DECIMAL(10,2) NOT NULL,
                descricao_servico TEXT NOT NULL,
                inss DECIMAL(10,2),
                irrf DECIMAL(10,2),
                valor_liquido DECIMAL(10,2),
                status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (prestador_id) REFERENCES prestadores(id)
            )
        ");

        return true;
    } catch (PDOException $e) {
        die("Erro ao inicializar o banco de dados: " . $e->getMessage());
    }
}

// Inicializa o banco de dados
initializeDatabase();
