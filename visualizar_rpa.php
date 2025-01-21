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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

// Buscar dados do RPA
$stmt = $pdo->prepare("
    SELECT r.*, p.nome, p.cpf, p.pis, p.endereco, p.cidade, p.estado
    FROM rpas r
    JOIN prestadores p ON r.prestador_id = p.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$rpa = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar configurações da empresa
$stmt = $pdo->query("SELECT * FROM empresa_config LIMIT 1");
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rpa) {
    header('Location: index.php');
    exit;
}

function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPA - <?php echo $rpa['numero_rpa']; ?></title>
    <style>
        /* Ajustes gerais */
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            margin: 0;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
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
            display: flex;
            justify-content: space-between;
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
            display: flex;
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
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 11pt;
        }

        .grid-item {
            display: table-cell;
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

        /* Botões de ação */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin-left: 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #22863a;
        }

        @media print {
            .action-buttons {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .box, .grid-item {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button onclick="window.print()" class="btn">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="pdf.php?id=<?php echo $rpa['id']; ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Baixar PDF
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="header">
        <?php if (!empty($empresa['logo_path']) && file_exists($empresa['logo_path'])): ?>
            <img src="<?php echo $empresa['logo_path']; ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <div class="header-title">RECIBO DE PAGAMENTO AUTÔNOMO - RPA</div>
        <div style="width: 150px;"></div>
    </div>

    <div class="rpa-info">
        <div>
            <strong>Nº:</strong> <?php echo str_pad($rpa['numero_rpa'], 8, '0', STR_PAD_LEFT); ?><br>
            <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($rpa['data_emissao'])); ?>
        </div>
        <div>
            <strong>R$ <?php echo number_format($rpa['valor_bruto'], 2, ',', '.'); ?></strong>
        </div>
    </div>

    <div class="box">
        <h2>Dados do Prestador de Serviço</h2>
        <div class="info-row">
            <strong>Nome:</strong> <?php echo htmlspecialchars($rpa['nome']); ?>
        </div>
        <div class="info-row">
            <strong>CPF:</strong> <?php echo htmlspecialchars($rpa['cpf']); ?>
        </div>
        <div class="info-row">
            <strong>PIS/PASEP:</strong>
        </div>
        <div class="info-row">
            <strong>Endereço:</strong>
        </div>
        <div class="info-row">
            <strong>Cidade/UF:</strong>
        </div>
    </div>

    <div class="box">
        <h2>Descrição dos Serviços</h2>
        <div class="descricao-box">
            <?php echo nl2br(htmlspecialchars($rpa['descricao_servico'])); ?>
        </div>
    </div>

    <?php
        // Determinar a alíquota do INSS
        $aliquotaINSS = $empresa['tipo_empresa'] === 'OS' ? '20%' : '11%';
    ?>
    <div class="grid">
        <div class="grid-item">
            <strong>Valor Bruto</strong><br>
            R$ <?php echo number_format($rpa['valor_bruto'], 2, ',', '.'); ?>
        </div>
        <div class="grid-item">
            <strong>INSS (<?php echo $aliquotaINSS; ?>)</strong><br>
            R$ <?php echo number_format($rpa['inss'], 2, ',', '.'); ?>
        </div>
        <div class="grid-item">
            <strong>IRRF</strong><br>
            R$ <?php echo number_format($rpa['irrf'], 2, ',', '.'); ?>
        </div>
        <div class="grid-item">
            <strong>Valor Líquido</strong><br>
            R$ <?php echo number_format($rpa['valor_liquido'], 2, ',', '.'); ?>
        </div>
    </div>

    <div class="footer">
        <p>
            Recebi da empresa <?php echo htmlspecialchars($empresa['nome']); ?>, inscrita no CNPJ sob<br>
            o nº <?php echo htmlspecialchars($empresa['cnpj']); ?>, a importância líquida de<br>
            R$ <?php echo number_format($rpa['valor_liquido'], 2, ',', '.'); ?> 
            (<?php echo extenso($rpa['valor_liquido']); ?>),<br>
            conforme discriminado acima.
        </p>

        <div class="assinatura">
            <?php echo htmlspecialchars($rpa['nome']); ?><br>
            CPF: <?php echo htmlspecialchars($rpa['cpf']); ?>
        </div>
    </div>
</body>
</html>

<?php
function extenso($valor) {
    $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
    $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");

    $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
    $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove");
    $u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");

    $z = 0;
    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    $cont = count($inteiro);
    
    for($i=0;$i<$cont;$i++)
        for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
            $inteiro[$i] = "0".$inteiro[$i];

    $fim = $cont - ($inteiro[$cont-1] > 0 ? 1 : 2);
    $rt = '';
    
    for ($i=0;$i<$cont;$i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
        $t = $cont-1-$i;
        $r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000")$z++; elseif ($z > 0) $z--;
        if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
        if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
    }

    return($rt ? trim($rt) : "zero");
}
