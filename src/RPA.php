<?php
namespace App;

class RPA {
    private $valorBruto;
    private $tipoEmpresa;

    public function __construct($valorBruto, $tipoEmpresa = 'normal') {
        $this->valorBruto = $valorBruto;
        $this->tipoEmpresa = $tipoEmpresa;
    }

    private function calcularINSS() {
        $aliquota = $this->tipoEmpresa === 'OS' ? 0.20 : 0.11;
        return $this->valorBruto * $aliquota;
    }

    private function calcularIRRF() {
        $baseCalculo = $this->valorBruto - $this->calcularINSS();
        
        // Tabela IRRF 2025
        if ($baseCalculo <= 2259.20) {
            return 0;
        } elseif ($baseCalculo <= 2826.65) {
            return ($baseCalculo * 0.075) - 169.44;
        } elseif ($baseCalculo <= 3751.05) {
            return ($baseCalculo * 0.15) - 381.44;
        } elseif ($baseCalculo <= 4664.68) {
            return ($baseCalculo * 0.225) - 662.77;
        } else {
            return ($baseCalculo * 0.275) - 896.00;
        }
    }

    public function getResumoRetencoes() {
        $inss = $this->calcularINSS();
        $irrf = $this->calcularIRRF();
        $valorLiquido = $this->valorBruto - $inss - $irrf;

        return [
            'valor_bruto' => $this->valorBruto,
            'inss' => $inss,
            'irrf' => $irrf,
            'valor_liquido' => $valorLiquido
        ];
    }
}
