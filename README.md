# Sistema de Geração de RPA

Sistema para geração e gerenciamento de Recibos de Pagamento Autônomo (RPA).

## Funcionalidades

- Cadastro de prestadores de serviço
- Geração de RPAs
- Cálculo automático de INSS e IRRF
- Geração de PDF dos recibos
- Gerenciamento de configurações da empresa

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Composer
- Extensões PHP necessárias:
  - PDO
  - PDO_MySQL
  - GD
  - mbstring

## Instalação

1. Clone o repositório:
```bash
git clone [URL_DO_REPOSITORIO]
cd CONCI
```

2. Instale as dependências via Composer:
```bash
composer install
```

3. Configure o banco de dados:
   - Copie o arquivo `config/database.example.php` para `config/database.php`
   - Edite o arquivo com suas configurações de banco de dados

4. Importe o banco de dados:
```bash
mysql -u seu_usuario -p < database/schema.sql
```

5. Configure as permissões:
   - Certifique-se que a pasta `uploads/` tem permissão de escrita

## Estrutura do Banco de Dados

- `prestadores`: Armazena informações dos prestadores de serviço
- `rpas`: Registros dos recibos gerados
- `empresa_config`: Configurações da empresa

## Uso

1. Acesse o sistema pelo navegador
2. Configure os dados da empresa em "Configurações"
3. Cadastre os prestadores de serviço
4. Gere os RPAs conforme necessário

## Desenvolvimento

- Padrão de código: PSR-12
- Banco de dados: MySQL com PDO
- Frontend: Bootstrap 5
- Geração de PDF: DomPDF
