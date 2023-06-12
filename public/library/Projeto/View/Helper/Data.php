<?php
    class Zend_View_Helper_Data extends Zend_View_Helper_Abstract {
    	
        public function data($pData,$tipo="normal") {
            try {
            	$data = '';
                $locale=Zend_Registry::get('Zend_Locale');
            	if($pData!=null && $pData!="0000-00-00") {
                     $dias_semana = array('DOM', 'SEG', 'TER',
                    'QUA', 'QUI', 'SEX', 'SAB');
                    $date=new Zend_Date($pData);
                         switch($tipo) {
                            case "comHorario":
                                $data = $date->get("dd/MM/YYYY HH:mm",$locale);
//                                $data=($date->get("dd")."/".$date->get("MM")."/".$date->get("yy")." ". $date->get("HH").":".$date->get("mm"));
                                break;
                            case "soHorario":
                            	$data = $date->get("HH:mm:ss",$locale);
                                //$data = explode(":",$pData);
                                //$data=$data[0]."h".$data[1];
//                                $data=($date->get("dd")."/".$date->get("MM")."/".$date->get("yy")." ". $date->get("HH").":".$date->get("mm"));
                                break;
                            case "normal":
                                $data = $date->get("dd/MM/YYYY",$locale);
                            break;
                            case "anosfull":
                                $data = $date->get("dd/MM/YYYY",$locale);
                            break;
                            case "diames":
                                $data = $date->get("dd/MM",$locale);
                                                        break;
                            case "diamesSemana":
                                $data = $date->get("dd/MM EE",$locale);
                                                        break;                            
                            default:
                                $data = $date->get($tipo,$locale);
                         }
            	 }
                 
	             return $data;
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }

        }
    }
?>
