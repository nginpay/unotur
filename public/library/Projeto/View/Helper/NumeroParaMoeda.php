<?php
    class Zend_View_Helper_NumeroParaMoeda extends Zend_View_Helper_Abstract {
        
        public function numeroParaMoeda($number) {
            if(!empty ($number)) {
                $separador1 = ",";
                $separador2 = ".";
                $valor = $number;


                $valor = number_format($valor, 2, ',', '.');
                return $valor;
            }
            else {
                return number_format(0,2,',','.');
            }
        }
    }
?>
