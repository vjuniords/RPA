-- Adiciona coluna tipo_empresa na tabela empresa_config
ALTER TABLE empresa_config
ADD COLUMN tipo_empresa ENUM('OS', 'normal') DEFAULT 'normal' AFTER cnpj;
