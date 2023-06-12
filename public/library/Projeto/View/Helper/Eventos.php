<?php
    class Zend_View_Helper_Eventos extends Zend_View_Helper_Abstract {
        public function eventos($data) {
            try {
            	$tblEvento = new Model_Evento();
            	$eventos = $tblEvento->fetchAll("data = '{$data}'");
            	$retorno = '';
            	if(count($eventos)>0){
            		foreach($eventos as $evento):
            			$retorno .= '
            						<li>
										<h3>'.$evento["titulo"].'</h3>
										<h4>'.$evento["horario"].'</h4>
										<p>
										'.nl2br($evento["texto"]).'
										</p>
									</li>
            					   ';
            		endforeach;
            	}
            	return $retorno;
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
?>
