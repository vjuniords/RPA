<?php
require_once 'config/database_init.php';

$config = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
    $config['username'], 
    $config['password']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buscar configurações existentes
$stmt = $pdo->query("SELECT * FROM empresa_config LIMIT 1");
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $logoPath = $empresa['logo_path'] ?? '';
        $marcaDaguaPath = $empresa['marca_dagua_path'] ?? '';

        // Upload do logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoTemp = $_FILES['logo']['tmp_name'];
            $logoName = 'logo_' . time() . '_' . $_FILES['logo']['name'];
            move_uploaded_file($logoTemp, $uploadDir . $logoName);
            $logoPath = $uploadDir . $logoName;
        }

        // Upload da marca d'água
        if (isset($_FILES['marca_dagua']) && $_FILES['marca_dagua']['error'] === UPLOAD_ERR_OK) {
            $marcaTemp = $_FILES['marca_dagua']['tmp_name'];
            $marcaName = 'marca_' . time() . '_' . $_FILES['marca_dagua']['name'];
            move_uploaded_file($marcaTemp, $uploadDir . $marcaName);
            $marcaDaguaPath = $uploadDir . $marcaName;
        }

        if ($empresa) {
            // Atualizar configurações existentes
            $stmt = $pdo->prepare("
                UPDATE empresa_config 
                SET nome = ?, cnpj = ?, tipo_empresa = ?, logo_path = ?, marca_dagua_path = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['nome'],
                $_POST['cnpj'],
                $_POST['tipo_empresa'],
                $logoPath,
                $marcaDaguaPath,
                $empresa['id']
            ]);
        } else {
            // Inserir novas configurações
            $stmt = $pdo->prepare("
                INSERT INTO empresa_config (nome, cnpj, tipo_empresa, logo_path, marca_dagua_path)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['nome'],
                $_POST['cnpj'],
                $_POST['tipo_empresa'],
                $logoPath,
                $marcaDaguaPath
            ]);
        }

        header("Location: configuracoes.php?success=1");
        exit;
    } catch (Exception $e) {
        $error = "Erro ao salvar as configurações: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações da Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Configurações da Empresa</h1>
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Configurações salvas com sucesso!
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome da Empresa</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo $empresa['nome'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="cnpj" class="form-label">CNPJ</label>
                                <input type="text" class="form-control" id="cnpj" name="cnpj" 
                                       value="<?php echo $empresa['cnpj'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="tipo_empresa" class="form-label">Tipo de Empresa</label>
                                <select class="form-select" id="tipo_empresa" name="tipo_empresa" required>
                                    <option value="normal" <?php echo ($empresa['tipo_empresa'] ?? 'normal') === 'normal' ? 'selected' : ''; ?>>
                                        Normal (INSS 11%)
                                    </option>
                                    <option value="OS" <?php echo ($empresa['tipo_empresa'] ?? '') === 'OS' ? 'selected' : ''; ?>>
                                        Organização Social (INSS 20%)
                                    </option>
                                </select>
                                <div class="form-text">
                                    Selecione o tipo de empresa para definir a alíquota do INSS:
                                    <ul>
                                        <li>Normal: INSS 11%</li>
                                        <li>Organização Social (OS): INSS 20%</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="logo" class="form-label">Logo da Empresa</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                <?php if (!empty($empresa['logo_path']) && file_exists($empresa['logo_path'])): ?>
                                    <img src="<?php echo $empresa['logo_path']; ?>" class="preview-image" alt="Logo atual">
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="marca_dagua" class="form-label">Marca d'Água</label>
                                <input type="file" class="form-control" id="marca_dagua" name="marca_dagua" accept="image/*">
                                <?php if (!empty($empresa['marca_dagua_path']) && file_exists($empresa['marca_dagua_path'])): ?>
                                    <img src="<?php echo $empresa['marca_dagua_path']; ?>" class="preview-image" alt="Marca d'água atual">
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#cnpj').mask('00.000.000/0000-00');
        });
    </script>
</body>
</html>
