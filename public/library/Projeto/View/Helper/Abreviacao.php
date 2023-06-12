<?php
    class Zend_View_Helper_Abreviacao extends Zend_View_Helper_Abstract {
    	public function abreviacao($str, $max=540, $rediscencia=''){    		
    		if(strlen($str)<$max)return $str;
    		$str = substr($str, 0, $max);
    		$str = substr($str, 0, strrpos($str," "));
    		return $str.$rediscencia;
    	}
    }
?>
