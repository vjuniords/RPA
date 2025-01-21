<?php
require_once 'config/database_init.php';

// Recebe os dados do RPA, prestador e empresa como parâmetros
function renderPDF($rpa, $prestador, $empresa) {
    $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/CONCI/uploads/' . $empresa['logo'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPA - <?php echo $rpa['numero_rpa']; ?></title>
    <style>
        @page {
            margin: 2cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Cabeçalho */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .logo {
            max-height: 70px;
            max-width: 200px;
        }

        .header-title {
            text-align: center;
            flex-grow: 1;
            margin: 0 20px;
            font-size: 16pt;
            font-weight: bold;
        }

        /* Info do RPA */
        .rpa-info {
            margin-bottom: 20px;
            font-size: 11pt;
        }

        /* Boxes */
        .box {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 20px;
            background: #fff;
        }

        .box h2 {
            font-size: 12pt;
            margin: 0 0 10px 0;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .info-row strong {
            width: 120px;
            display: inline-block;
        }

        /* Descrição */
        .descricao-box {
            min-height: 100px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            margin-top: 10px;
        }

        /* Grid de valores */
        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 11pt;
        }

        .grid-item {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            width: 25%;
            background: #f9f9f9;
        }

        .grid-item strong {
            display: block;
            margin-bottom: 5px;
            font-size: 11pt;
        }

        /* Rodapé e assinatura */
        .footer {
            text-align: center;
            margin-top: 40px;
            line-height: 1.6;
        }

        .footer p {
            margin-bottom: 40px;
        }

        .assinatura {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 10px;
            display: inline-block;
            min-width: 400px;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if (!empty($empresa['logo'])): ?>
            <img src="<?php echo $logoPath; ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <div class="header-title">RECIBO DE PAGAMENTO AUTÔNOMO - RPA</div>
    </div>

    <div class="rpa-info">
        <strong>Nº:</strong> <?php echo htmlspecialchars($rpa['numero_rpa']); ?><br>
        <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($rpa['data_emissao'])); ?><br>
        <strong>R$</strong> <?php echo number_format($rpa['valor_bruto'], 2, ',', '.'); ?>
    </div>

    <div class="box">
        <h2>Dados do Prestador de Serviço</h2>
        <div class="info-row">
            <strong>Nome:</strong> <?php echo htmlspecialchars($prestador['nome'] ?? ''); ?>
        </div>
        <div class="info-row">
            <strong>CPF:</strong> <?php echo htmlspecialchars($prestador['cpf'] ?? ''); ?>
        </div>
        <div class="info-row">
            <strong>PIS/PASEP:</strong> <?php echo htmlspecialchars($prestador['pis'] ?? ''); ?>
        </div>
        <div class="info-row">
            <strong>Endereço:</strong> <?php echo htmlspecialchars($prestador['endereco'] ?? ''); ?>
        </div>
        <div class="info-row">
            <strong>Cidade/UF:</strong> <?php echo htmlspecialchars(($prestador['cidade'] ?? '') . '/' . ($prestador['estado'] ?? '')); ?>
        </div>
    </div>

    <div class="box">
        <h2>Descrição dos Serviços</h2>
        <div class="descricao-box">
            <?php echo nl2br(htmlspecialchars($rpa['descricao'] ?? '')); ?>
        </div>
    </div>

    <div class="grid">
        <div class="grid-item">
            <strong>Valor Bruto</strong>
            R$ <?php echo number_format($rpa['valor_bruto'] ?? 0, 2, ',', '.'); ?>
        </div>
        <div class="grid-item">
            <strong>INSS (<?php echo $rpa['aliquota_inss'] ?? '0'; ?>%)</strong>
            R$ <?php echo number_format($rpa['valor_inss'] ?? 0, 2, ',', '.'); ?>
        </div>
        <div class="grid-item">
            <strong>IRRF</strong>
            R$ <?php echo number_format($rpa['valor_irrf'] ?? 0, 2, ',', '.'); ?>
        </div>
        <div class="grid-item">
            <strong>Valor Líquido</strong>
            R$ <?php echo number_format($rpa['valor_liquido'] ?? 0, 2, ',', '.'); ?>
        </div>
    </div>

    <div class="footer">
        <p>
            Recebi da empresa <?php echo htmlspecialchars($empresa['nome'] ?? ''); ?>, inscrita no CNPJ sob<br>
            o nº <?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>, a importância líquida de<br>
            R$ <?php echo number_format($rpa['valor_liquido'] ?? 0, 2, ',', '.'); ?>
            (<?php echo htmlspecialchars($rpa['valor_liquido_extenso'] ?? ''); ?>)
        </p>

        <div class="assinatura">
            <?php echo htmlspecialchars($prestador['nome'] ?? ''); ?><br>
            CPF: <?php echo htmlspecialchars($prestador['cpf'] ?? ''); ?>
        </div>
    </div>
</body>
</html>
<?php
}
?>
