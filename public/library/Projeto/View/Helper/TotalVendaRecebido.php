<?php
    class Zend_View_Helper_TotalVendaRecebido extends Zend_View_Helper_Abstract {
        
        public function totalVendaRecebido($venda, $pacote = false) {
            $tblVendaReceber = new Model_VendaAReceber();
            $querie = "venda = $venda";
            
            if($pacote){
                $querie.= "AND venda in (SELECT venda FROM venda_produto WHERE pacote = $pacote) ";    
            }
            
    	    $result =  $tblVendaReceber->fetchAll($querie);
    	        	    
    	    $totalRecebido = 0;
    	    if(count($result) > 0){
    	        foreach($result as $dado):
    	            if($dado["valorcambio"] > 0){
    	        		$totalRecebido += bcdiv($dado["valorpago"], $dado["valorcambio"], 2);
    	        	} else {
    	        		$totalRecebido += $dado["valorpago"];
    	        	}     	        		   
    	        endforeach;    
    	    }
    	    
    	    return $totalRecebido;
        }
    }
?>
