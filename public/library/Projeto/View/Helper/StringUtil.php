<?php
/**
 * StringUtil 
 * 
 */
class Projeto_View_Helper_StringUtil {
	/**
	 * View Padrao
	 * @var Zend_View_Interface 
	 */
	public $view;
	/**
	 * Constructor do Helper
	 */
	public function stringUtil() {
		// TODO Auto-generated Wiv_View_Helper_ImagemUtil::imagemUtil() helper 
		return $this;
	}
	public function limparString($str, $espaco = "_"){
		$comAcentos = array("\\", "/", ".", ":", ";", " ", "&","Š","Œ","Ž","š","œ","ž","¥","µ","À","Á","Â","Ã","Ä","Å","Æ","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ð","Ñ","Ò","Ó","Ô","Õ","Ö","Ø","Ù","Ú","Û","Ü","Ý","ß","à","á","â","ã","ä","å","æ","ç","è","é","ê","ë","ì","í","î","ï","ð","ñ","ò","ó","ô","õ","ö","ø","ù","ú","û","ü","ý","ÿ");
		$semAcentos = array($espaco, $espaco, $espaco, $espaco, $espaco, $espaco, "e","S","O","Z","s","o","z","Y","u","A","A","A","A","A","A","A","C","E","E","E","E","I","I","I","I","D","N","O","O","O","O","O","O","U","U","U","U","Y","s","a","a","a","a","a","a","a","c","e","e","e","e","i","i","i","i","o","n","o","o","o","o","o","o","u","u","u","u","y","y");
		$str = str_replace($comAcentos, $semAcentos, $str);
		return $str;
	}
	/**
	 * Metodo que seja a view do plugin
	 * 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
