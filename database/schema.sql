-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS rpa_system;
USE rpa_system;

-- Tabela de prestadores
CREATE TABLE IF NOT EXISTS prestadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    pis VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(100),
    estado CHAR(2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de configurações da empresa
CREATE TABLE IF NOT EXISTS empresa_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(18),
    tipo_empresa ENUM('OS', 'normal') DEFAULT 'normal',
    logo_path VARCHAR(255),
    marca_dagua_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de RPAs
CREATE TABLE IF NOT EXISTS rpas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prestador_id INT NOT NULL,
    numero_rpa VARCHAR(20) NOT NULL,
    data_emissao DATE NOT NULL,
    valor_bruto DECIMAL(10,2) NOT NULL,
    inss DECIMAL(10,2) NOT NULL,
    irrf DECIMAL(10,2) NOT NULL,
    valor_liquido DECIMAL(10,2) NOT NULL,
    descricao_servico TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prestador_id) REFERENCES prestadores(id)
);
