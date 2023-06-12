<?php
    class Zend_View_Helper_MoedaParaNumero extends Zend_View_Helper_Abstract {
                
        public function moedaParaNumero($valor) {
            if(!empty ($valor)) {
                $valor = preg_replace("([^0-9\,])", "", $valor);
                $valor = str_replace(",", ".",$valor);
                return $valor;
            }
        }
    }
?>
