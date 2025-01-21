<?php
require_once 'vendor/autoload.php';

use App\RPA;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valorBruto = filter_input(INPUT_POST, 'valor_bruto', FILTER_VALIDATE_FLOAT);
    
    if ($valorBruto !== false) {
        $rpa = new RPA($valorBruto);
        echo json_encode($rpa->getResumoRetencoes());
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Valor bruto inválido']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
