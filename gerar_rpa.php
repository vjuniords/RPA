<?php
require_once 'vendor/autoload.php';
require_once 'config/database_init.php';

use App\RPA;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestadorId = filter_input(INPUT_POST, 'prestador_id', FILTER_VALIDATE_INT);
    $valorBruto = filter_input(INPUT_POST, 'valor_bruto', FILTER_VALIDATE_FLOAT);
    $descricaoServico = filter_input(INPUT_POST, 'descricao_servico', FILTER_SANITIZE_STRING);

    if (!$prestadorId || !$valorBruto || !$descricaoServico) {
        header('Location: index.php?error=dados_invalidos');
        exit;
    }

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

        // Criar instância de RPA com o tipo de empresa correto
        $valorBruto = str_replace(['.', ','], ['', '.'], $_POST['valor_bruto']);
        $rpa = new RPA($valorBruto, $empresa['tipo_empresa'] ?? 'normal');
        $retencoes = $rpa->getResumoRetencoes();

        // Gerar número do RPA
        $stmt = $pdo->query("SELECT COUNT(*) + 1 as proximo FROM rpas");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $numeroRPA = str_pad($resultado['proximo'], 8, '0', STR_PAD_LEFT);

        // Inserir RPA no banco
        $stmt = $pdo->prepare("
            INSERT INTO rpas (
                prestador_id, numero_rpa, data_emissao, valor_bruto, 
                inss, irrf, valor_liquido, descricao_servico
            ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $prestadorId,
            $numeroRPA,
            $retencoes['valor_bruto'],
            $retencoes['inss'],
            $retencoes['irrf'],
            $retencoes['valor_liquido'],
            $descricaoServico
        ]);

        // Redireciona para visualização do RPA
        header("Location: visualizar_rpa.php?id=" . $pdo->lastInsertId());
        exit;

    } catch (PDOException $e) {
        header('Location: index.php?error=erro_banco');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
