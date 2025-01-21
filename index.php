<?php
require_once 'vendor/autoload.php';
require_once 'config/database_init.php';

try {
    $config = require 'config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar configurações da empresa
    $stmt = $pdo->query("SELECT * FROM empresa_config LIMIT 1");
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Buscar prestadores
    $stmt = $pdo->query("SELECT id, nome, cpf FROM prestadores ORDER BY nome");
    $prestadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Determinar a alíquota do INSS
    $aliquotaINSS = $empresa['tipo_empresa'] === 'OS' ? '20%' : '11%';
} catch (PDOException $e) {
    $prestadores = [];
    $aliquotaINSS = '11%'; // valor padrão
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Geração de RPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Geração de RPA</h1>
                    <div>
                        <a href="configuracoes.php" class="btn btn-info me-2">
                            <i class="bi bi-gear"></i> Configurações da Empresa
                        </a>
                        <a href="prestadores.php" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Gestão de Prestadores
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php
                        $error = $_GET['error'];
                        switch($error) {
                            case 'dados_invalidos':
                                echo "Dados inválidos. Por favor, verifique os campos.";
                                break;
                            case 'erro_banco':
                                echo "Erro ao conectar ao banco de dados.";
                                break;
                            default:
                                echo "Ocorreu um erro.";
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($prestadores)): ?>
                    <div class="alert alert-warning">
                        Nenhum prestador cadastrado. 
                        <a href="prestadores.php" class="alert-link">Cadastre um prestador</a> 
                        antes de gerar um RPA.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <form id="rpaForm" method="POST" action="gerar_rpa.php">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="prestador" class="form-label">Prestador de Serviço</label>
                                        <select class="form-select" id="prestador" name="prestador_id" required>
                                            <option value="">Selecione um prestador</option>
                                            <?php foreach ($prestadores as $prestador): ?>
                                                <option value="<?php echo $prestador['id']; ?>">
                                                    <?php echo htmlspecialchars($prestador['nome'] . ' - CPF: ' . $prestador['cpf']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="valor_bruto" class="form-label">Valor Bruto</label>
                                        <input type="number" step="0.01" class="form-control" id="valor_bruto" name="valor_bruto" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição do Serviço</label>
                                    <textarea class="form-control" id="descricao" name="descricao_servico" rows="3" required></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="inss" class="form-label">INSS (<?php echo $aliquotaINSS; ?>)</label>
                                        <input type="text" class="form-control" id="inss" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="irrf" class="form-label">IRRF</label>
                                        <input type="text" class="form-control" id="irrf" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="valor_liquido" class="form-label">Valor Líquido</label>
                                        <input type="text" class="form-control" id="valor_liquido" readonly>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Gerar RPA</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-file-earmark-text"></i> 
                            Recibos
                        </h5>
                        <p class="card-text">Visualize e baixe os recibos gerados, organizados por prestador, ano e mês.</p>
                        <a href="listar_recibos.php" class="btn btn-primary">
                            <i class="bi bi-list"></i> Listar Recibos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#valor_bruto').on('input', function() {
                var valorBruto = parseFloat($(this).val()) || 0;
                var aliquotaINSS = <?php echo $empresa['tipo_empresa'] === 'OS' ? 0.20 : 0.11; ?>;
                
                // Calcula INSS
                var inss = valorBruto * aliquotaINSS;
                
                // Calcula base do IR
                var baseIR = valorBruto - inss;
                
                // Calcula IR
                var ir = 0;
                if (baseIR > 4664.68) {
                    ir = (baseIR * 0.275) - 896.00;
                } else if (baseIR > 3751.05) {
                    ir = (baseIR * 0.225) - 662.77;
                } else if (baseIR > 2826.65) {
                    ir = (baseIR * 0.15) - 381.44;
                } else if (baseIR > 2259.20) {
                    ir = (baseIR * 0.075) - 169.44;
                }
                
                if (ir < 0) ir = 0;
                
                // Calcula valor líquido
                var valorLiquido = valorBruto - inss - ir;
                
                // Atualiza os campos
                $('#inss').val(inss.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
                $('#irrf').val(ir.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
                $('#valor_liquido').val(valorLiquido.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
            });
        });
    </script>
</body>
</html>
