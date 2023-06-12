<?php
    class Zend_View_Helper_Resize extends Zend_View_Helper_Abstract {
        public function resize($filename, $width=null, $height=null) {
            try {
                $image = PhpThumb_Factory::create(getcwd().$filename, array('jpegQuality' => 100));
                $image->adaptiveResize($width,$height);
                //$image->crop(0, $height, $width, $height);
                //$image->cropFromCenter($width, $height);
                $pathinfo = pathinfo($filename);
                $arquivoCache = $pathinfo["dirname"]."/".$pathinfo["filename"]."-cache.".$pathinfo["extension"];
                $image->save(getcwd().$arquivoCache);
                return $arquivoCache;
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
?>
