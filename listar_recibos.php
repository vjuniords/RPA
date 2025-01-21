<?php
require_once 'config/database_init.php';

$config = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
    $config['username'], 
    $config['password']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buscar prestadores para o filtro
$prestadores = $pdo->query("SELECT id, nome FROM prestadores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Filtros
$where = "1=1";
$params = [];

if (!empty($_GET['prestador_id'])) {
    $where .= " AND p.id = ?";
    $params[] = $_GET['prestador_id'];
}

if (!empty($_GET['ano'])) {
    $where .= " AND YEAR(r.data_emissao) = ?";
    $params[] = $_GET['ano'];
}

if (!empty($_GET['mes'])) {
    $where .= " AND MONTH(r.data_emissao) = ?";
    $params[] = $_GET['mes'];
}

// Buscar RPAs
$stmt = $pdo->prepare("
    SELECT r.*, p.nome as prestador_nome
    FROM rpas r
    JOIN prestadores p ON r.prestador_id = p.id
    WHERE $where
    ORDER BY r.data_emissao DESC
");
$stmt->execute($params);
$rpas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Anos disponíveis
$anos = $pdo->query("SELECT DISTINCT YEAR(data_emissao) as ano FROM rpas ORDER BY ano DESC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Recibos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Recibos Gerados</h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="filters">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="prestador_id" class="form-label">Prestador</label>
                    <select class="form-select" id="prestador_id" name="prestador_id">
                        <option value="">Todos</option>
                        <?php foreach ($prestadores as $prestador): ?>
                            <option value="<?php echo $prestador['id']; ?>" 
                                    <?php echo isset($_GET['prestador_id']) && $_GET['prestador_id'] == $prestador['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prestador['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="ano" class="form-label">Ano</label>
                    <select class="form-select" id="ano" name="ano">
                        <option value="">Todos</option>
                        <?php foreach ($anos as $ano): ?>
                            <option value="<?php echo $ano; ?>" 
                                    <?php echo isset($_GET['ano']) && $_GET['ano'] == $ano ? 'selected' : ''; ?>>
                                <?php echo $ano; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="mes" class="form-label">Mês</label>
                    <select class="form-select" id="mes" name="mes">
                        <option value="">Todos</option>
                        <?php 
                        $meses = [
                            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
                            4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
                            10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                        ];
                        foreach ($meses as $num => $nome): ?>
                            <option value="<?php echo $num; ?>" 
                                    <?php echo isset($_GET['mes']) && $_GET['mes'] == $num ? 'selected' : ''; ?>>
                                <?php echo $nome; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nº RPA</th>
                        <th>Data</th>
                        <th>Prestador</th>
                        <th>Valor Bruto</th>
                        <th>Valor Líquido</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rpas as $rpa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rpa['numero_rpa']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($rpa['data_emissao'])); ?></td>
                            <td><?php echo htmlspecialchars($rpa['prestador_nome']); ?></td>
                            <td>R$ <?php echo number_format($rpa['valor_bruto'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($rpa['valor_liquido'], 2, ',', '.'); ?></td>
                            <td>
                                <a href="visualizar_rpa.php?id=<?php echo $rpa['id']; ?>" class="btn btn-sm btn-info" title="Visualizar">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="pdf.php?id=<?php echo $rpa['id']; ?>" class="btn btn-sm btn-success" title="Baixar PDF">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
