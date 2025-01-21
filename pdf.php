<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die('ID não fornecido');
}

try {
    // Carrega configuração do banco
    $config = require 'config/database.php';
    
    // Conexão com o banco
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);
    
    // Busca dados do RPA
    $sql = "
        SELECT 
            r.numero_rpa,
            r.data_emissao,
            r.valor_bruto,
            r.valor_liquido,
            r.inss,
            r.irrf,
            r.descricao_servico as descricao,
            p.nome,
            p.cpf,
            p.pis,
            p.endereco,
            p.cidade,
            p.estado,
            e.nome as empresa_nome,
            e.cnpj as empresa_cnpj
        FROM rpas r
        JOIN prestadores p ON r.prestador_id = p.id
        CROSS JOIN empresa_config e
        WHERE r.id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['id']]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dados) {
        die('RPA não encontrado');
    }
    
    // Calcula as alíquotas
    $aliquota_inss = ($dados['valor_bruto'] > 0) ? ($dados['inss'] / $dados['valor_bruto'] * 100) : 0;
    $aliquota_irrf = ($dados['valor_bruto'] > 0) ? ($dados['irrf'] / $dados['valor_bruto'] * 100) : 0;
    
    // Configura o DOMPDF
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    
    // Gera o HTML
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>RPA ' . $dados['numero_rpa'] . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 2cm;
                font-size: 12pt;
                line-height: 1.3;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #000;
                padding-bottom: 20px;
            }
            .title {
                font-size: 16pt;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .box {
                border: 1px solid #000;
                padding: 15px;
                margin-bottom: 20px;
            }
            .box h2 {
                font-size: 12pt;
                margin: 0 0 10px 0;
                padding-bottom: 5px;
                border-bottom: 1px solid #ccc;
            }
            .info-row {
                margin-bottom: 5px;
            }
            .info-row strong {
                display: inline-block;
                width: 120px;
            }
            .valores {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .valores td {
                border: 1px solid #000;
                padding: 10px;
                text-align: center;
                width: 25%;
            }
            .valores strong {
                display: block;
                margin-bottom: 5px;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
            }
            .assinatura {
                margin-top: 50px;
                border-top: 1px solid #000;
                padding-top: 10px;
                display: inline-block;
                min-width: 300px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">RECIBO DE PAGAMENTO AUTÔNOMO - RPA</div>
            <div>Nº ' . $dados['numero_rpa'] . ' - Data: ' . date('d/m/Y', strtotime($dados['data_emissao'])) . '</div>
        </div>
        
        <div class="box">
            <h2>Dados do Prestador de Serviço</h2>
            <div class="info-row"><strong>Nome:</strong> ' . htmlspecialchars($dados['nome']) . '</div>
            <div class="info-row"><strong>CPF:</strong> ' . htmlspecialchars($dados['cpf']) . '</div>
            <div class="info-row"><strong>PIS/PASEP:</strong> ' . htmlspecialchars($dados['pis']) . '</div>
            <div class="info-row"><strong>Endereço:</strong> ' . htmlspecialchars($dados['endereco']) . '</div>
            <div class="info-row"><strong>Cidade/UF:</strong> ' . htmlspecialchars($dados['cidade'] . '/' . $dados['estado']) . '</div>
        </div>
        
        <div class="box">
            <h2>Descrição dos Serviços</h2>
            <div style="min-height: 50px;">
                ' . nl2br(htmlspecialchars($dados['descricao'])) . '
            </div>
        </div>
        
        <table class="valores">
            <tr>
                <td>
                    <strong>Valor Bruto</strong>
                    R$ ' . number_format($dados['valor_bruto'], 2, ',', '.') . '
                </td>
                <td>
                    <strong>INSS (' . number_format($aliquota_inss, 1) . '%)</strong>
                    R$ ' . number_format($dados['inss'], 2, ',', '.') . '
                </td>
                <td>
                    <strong>IRRF (' . number_format($aliquota_irrf, 1) . '%)</strong>
                    R$ ' . number_format($dados['irrf'], 2, ',', '.') . '
                </td>
                <td>
                    <strong>Valor Líquido</strong>
                    R$ ' . number_format($dados['valor_liquido'], 2, ',', '.') . '
                </td>
            </tr>
        </table>
        
        <div class="footer">
            <p>
                Recebi da empresa ' . htmlspecialchars($dados['empresa_nome']) . ', inscrita no CNPJ sob<br>
                o nº ' . htmlspecialchars($dados['empresa_cnpj']) . ', a importância líquida de<br>
                R$ ' . number_format($dados['valor_liquido'], 2, ',', '.') . '
            </p>
            
            <div class="assinatura">
                ' . htmlspecialchars($dados['nome']) . '<br>
                CPF: ' . htmlspecialchars($dados['cpf']) . '
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Carrega o HTML
    $dompdf->loadHtml($html);
    
    // Configura o papel
    $dompdf->setPaper('A4', 'portrait');
    
    // Renderiza o PDF
    $dompdf->render();
    
    // Força o download
    $dompdf->stream('RPA_' . $dados['numero_rpa'] . '.pdf', [
        'Attachment' => true
    ]);
    
} catch (Exception $e) {
    die('Erro: ' . $e->getMessage());
}
