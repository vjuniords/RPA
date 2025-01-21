<?php
require_once 'vendor/autoload.php';
require_once 'config/database_init.php';

$config = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
    $config['username'], 
    $config['password']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mensagem = '';

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO prestadores (
                nome, cpf, pis, endereco, cidade, 
                estado, cep, telefone, email
            ) VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $_POST['nome'],
            $_POST['cpf'],
            $_POST['pis'],
            $_POST['endereco'],
            $_POST['cidade'],
            $_POST['estado'],
            $_POST['cep'],
            $_POST['telefone'],
            $_POST['email']
        ]);

        $mensagem = 'Prestador cadastrado com sucesso!';
    } catch (PDOException $e) {
        $mensagem = 'Erro ao cadastrar prestador: ' . $e->getMessage();
    }
}

// Buscar prestadores cadastrados
$prestadores = $pdo->query("SELECT * FROM prestadores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Prestadores - Sistema RPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestão de Prestadores</h1>
            <a href="index.php" class="btn btn-secondary">Voltar para Geração de RPA</a>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulário de Cadastro -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Novo Prestador</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo*</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="cpf" class="form-label">CPF*</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" required>
                            </div>
                            <div class="mb-3">
                                <label for="pis" class="form-label">PIS/PASEP</label>
                                <input type="text" class="form-control" id="pis" name="pis">
                            </div>
                            <div class="mb-3">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco">
                            </div>
                            <div class="mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade">
                            </div>
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Selecione...</option>
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep">
                            </div>
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <button type="submit" class="btn btn-primary">Cadastrar Prestador</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lista de Prestadores -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Prestadores Cadastrados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Telefone</th>
                                        <th>E-mail</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prestadores as $prestador): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($prestador['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($prestador['cpf']); ?></td>
                                            <td><?php echo htmlspecialchars($prestador['telefone']); ?></td>
                                            <td><?php echo htmlspecialchars($prestador['email']); ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detalhesModal<?php echo $prestador['id']; ?>">
                                                    Detalhes
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal de Detalhes -->
                                        <div class="modal fade" id="detalhesModal<?php echo $prestador['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detalhes do Prestador</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($prestador['nome']); ?></p>
                                                        <p><strong>CPF:</strong> <?php echo htmlspecialchars($prestador['cpf']); ?></p>
                                                        <p><strong>PIS/PASEP:</strong> <?php echo htmlspecialchars($prestador['pis']); ?></p>
                                                        <p><strong>Endereço:</strong> <?php echo htmlspecialchars($prestador['endereco']); ?></p>
                                                        <p><strong>Cidade:</strong> <?php echo htmlspecialchars($prestador['cidade']); ?></p>
                                                        <p><strong>Estado:</strong> <?php echo htmlspecialchars($prestador['estado']); ?></p>
                                                        <p><strong>CEP:</strong> <?php echo htmlspecialchars($prestador['cep']); ?></p>
                                                        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($prestador['telefone']); ?></p>
                                                        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($prestador['email']); ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Máscara para CPF
            $('#cpf').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                    $(this).val(value);
                }
            });

            // Máscara para PIS
            $('#pis').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d{5})(\d{2})(\d{1})/, '$1.$2.$3-$4');
                    $(this).val(value);
                }
            });

            // Máscara para CEP
            $('#cep').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length <= 8) {
                    value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
                    $(this).val(value);
                }
            });

            // Máscara para telefone
            $('#telefone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length <= 11) {
                    if (value.length === 11) {
                        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    } else {
                        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                    }
                    $(this).val(value);
                }
            });
        });
    </script>
</body>
</html>
