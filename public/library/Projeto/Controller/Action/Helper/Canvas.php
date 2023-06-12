<?php

/**
 *
 * Classe para manipulacao de imagens utilizando a extensao GD
 * e recursos avancados de filtros. Requer PHP 5 ou superior.
 * @version    1.0 $ 2010-10-17 19:11:51 $
*/

class Projeto_Controller_Action_Helper_Canvas extends Zend_Controller_Action_Helper_Abstract {
	/**
	 * Instancia do Plugin Loader
	 *
	 * @var Zend_Loader_PluginLoader
	 */
	public $pluginLoader;
	/**
	 * Constructor: initialize plugin loader
	 *
	 * @return void
	 */
	public function __construct($origem = '') {
		$this->pluginLoader = new Zend_Loader_PluginLoader();
		$this->origem = $origem;
		if ( $this->origem )
		{
			$this->dados();
		}
		// RGB padrao -> branco
		$this->rgb( 255, 255, 255 );
	}
	/**
	 * Strategy pattern: call helper as broker method
	 */
	public function direct() {
		return $this;
	}
    /**
     * Variaveis para armazenamento de arquivos/imgs
     **/
    private $origem, $img, $img_temp;
    /**
     * Armazenam as dimensoes da imagem atual e da nova imagem caso exista
     **/
    private $largura, $altura, $nova_largura, $nova_altura, $tamanho_html;
    /**
     * Informacoes sobre o arquivo enviado e diretorio
     **/
    private $formato, $extensao, $tamanho, $arquivo, $diretorio;
    /**
     * Array RGB para resize com preenchimendo do fundo
     **/
    private $rgb;
    /**
     * Coordenadas para posicionamento do crop
     **/
    private $posicao_crop;
    /**
     * Coordenadas para efeito transparencia
     */
    private $transparencia;
     /**
      * Retorna dados da imagem
      * @return void
      **/
     private function dados()
     {
          // verifica se imagem existe
          if ( is_file( $this->origem ) )
          {
               // dados do arquivo
               $this->dadosArquivo();
               // verifica se e imagem
               if ( !$this->eImagem() )
               {
                    trigger_error( 'Erro: Arquivo '.$this->origem.' no  uma imagem!', E_USER_ERROR );
               }
               else
               {
                    // busca dimensoes da imagem enviada
                    $this->dimensoes();
                    // cria imagem para php
                    $this->criaImagem();
               }
          }
          else
          {
               trigger_error( 'Erro: Arquivo de imagem nao encontrado!', E_USER_ERROR );
          }
     } // fim dadosImagem
     /**
      * Carrega uma nova imagem, fora do construtor
      * @param String caminho da imagem a ser carregada
      * @return Object instancia atual do objeto, para metodos encadeados
      **/
     public function carrega( $origem = '' )
     {
          $this->origem = $origem;
          $this->dados();
          return $this;
     } // fim carrega
     /**
      * Busca dimensoes e formato real da imagem
      * @return void
      **/
     private function dimensoes()
     {
    $dimensoes                  = getimagesize( $this->origem );
    $this->largura              = $dimensoes[0];
    $this->altura               = $dimensoes[1];
    /**
    * 1 = gif, 2 = jpeg, 3 = png, 6 = BMP
    * http://br2.php.net/manual/en/function.exif-imagetype.php
    **/
    $this->formato               = $dimensoes[2];
    $this->tamanho_html          = $dimensoes[3];
     } // fim dimensoes
     /**
      * Busca dados do arquivo
      * @return void
      **/
     private function dadosArquivo()
     {
          // imagem de origem
          $pathinfo            = pathinfo( $this->origem );
          $this->extensao      = strtolower( $pathinfo['extension'] );
          $this->arquivo       = $pathinfo['basename'];
          $this->diretorio     = $pathinfo['dirname'];
     } // fim dadosArquivo
     /**
      * Verifica se o arquivo indicado e uma imagem
      * @return Boolean true/false
      **/
     private function eImagem()
     {
          // filtra extensao
          $valida = getimagesize( $this->origem );
          if ( !is_array( $valida ) || empty( $valida ) )
          {
               return false;
          }
          else
          {
               return true;
          }
     } // fim validaImagem
     /**
      * Cria uma nova imagem para ser trabalhada com textos, etc.
      * OBS: a cor da imagem deve ser passada antes, via rgb() ou hex()
      * @param String $largura da imagem a ser criada
      * @param String $altura da imagem a ser criada
      * @return Object instancia atual do objeto, para metodos encadeados
      **/
     public function novaImagem( $largura, $altura )
     {
          $this->largura     = $largura;
          $this->altura     = $altura;
          $this->img = imagecreatetruecolor( $this->largura, $this->altura );
          $cor_fundo = imagecolorallocate( $this->img, $this->rgb[0], $this->rgb[1], $this->rgb[2] );
          imagefill( $this->img, 0, 0, $cor_fundo );
          $this->extensao = 'jpg';
          return $this;
    } // fim novaImagem
    /**
     * Carrega uma imagem via URL
     * OBS: depente das configuracoes do servidor para acesso remoto de arquivos
     * @param String $url endereco da imagem
      * @return Object instancia atual do objeto, para metodos encadeados
     **/
     public function carregaUrl( $url )
     {
          $this->origem = $url;
          $pathinfo = pathinfo( $this->origem );
          $this->extensao = strtolower( $pathinfo['extension'] );
          switch( $this->extensao )
          {
               case 'jpg':
               case 'jpeg':
                    $this->formato = 2;
                    break;
               case 'gif':
                    $this->formato = 1;
                    break;
               case 'png':
                    $this->formato = 3;
                    break;
               case 'bmp':
                    $this->formato = 6;
                    break;
               default:
                    break;
     }
          $this->criaImagem();
     $this->largura     = imagesx( $this->img );
          $this->altura     = imagesy( $this->img );
          return $this;
     } // fim carregaUrl
     /**
      * Cria objeto de imagem para manipulacao no GD
      * @return void
      **/
     private function criaImagem()
     {
          switch ( $this->formato )
          {
               case 1:
                    $this->img = imagecreatefromgif( $this->origem );
                    $this->extensao = 'gif';
                    break;
               case 2:
                    $this->img = imagecreatefromjpeg( $this->origem );
                    $this->extensao = 'jpg';
                    break;
               case 3:
                    $this->img = imagecreatefrompng( $this->origem );
                    $this->extensao = 'png';
                    break;
               case 6:
                    $this->img = imagecreatefrombmp( $this->origem );
                    $this->extensao = 'bmp';
                    break;
               default:
                    trigger_error( 'Arquivo invalido!', E_USER_ERROR );
                    break;
          }
     } // fim criaImagem
     /**
      * Armazena os valores RGB para redimensionamento com preenchimento
      * @param Valores R, G e B
      * @return Object instancia atual do objeto, para metodos encadeados
      **/
     public function rgb( $r, $g, $b )
     {
          $this->rgb = array( $r, $g, $b );
          return $this;
     } // fim rgb
     /**
      * Converte hexadecimal para RGB
      * @param String $cor cor hexadecimal
      * @return Object instancia atual do objeto, para metodos encadeados
      **/
     public function hexa( $cor )
     {
          $cor = str_replace( '#', '', $cor );
          if( strlen( $cor ) == 3 ) $cor .= $cor; // #fff, #000 etc.
          $this->rgb = array(
            hexdec( substr( $cor, 0, 2 ) ),
            hexdec( substr( $cor, 2, 2 ) ),
            hexdec( substr( $cor, 4, 2 ) ),
          );
          return $this;
     }  // fim hexa
     /**
      * Armazena posicoes x e y para crop
      * @param Array valores x e y
      * @return Object instancia atual do objeto, para metodos encadeados
      **/
     public function posicaoCrop( $x, $y )
     {
          $this->posicao_crop = array( $x, $y, $this->largura, $this->altura );
          return $this;
     } // fim posicao_crop
     /**
      * Redimensiona imagem
      * @param Int $nova_largura valor em pixels da nova largura da imagem
      * @param Int $nova_altura valor em pixels da nova altura da imagem
      * @param String $tipo metodo para redimensionamento (padrao [vazio], preenchimento ou crop)
      * @return Object instancia atual do objeto, para metodos encadeados
      **/
     public function redimensiona( $nova_largura = 0, $nova_altura = 0, $tipo = '', $transparencia = false)
     {
          // seta variaveis passadas via parametro
          $this->nova_largura          = $nova_largura;
          $this->nova_altura          = $nova_altura;
          $this->transparencia        = $transparencia;
          // verifica se passou altura ou largura como porcentagem
          // largura %
          $pos = strpos( $this->nova_largura, '%' );
          if( $pos !== false && $pos > 0 )
          {
               $porcentagem               = ( ( int ) str_replace( '%', '', $this->nova_largura ) ) / 100;
               $this->nova_largura          = round( $this->largura * $porcentagem );
          }
          // altura %
          $pos = strpos( $this->nova_altura, '%' );
          if( $pos !== false && $pos > 0 )
          {
               $porcentagem               = ( ( int ) str_replace( '%', '', $this->nova_altura ) ) / 100;
               $this->nova_altura          = $this->altura * $porcentagem;
          }
          // define se se passou nova largura ou altura
          if ( !$this->nova_largura && !$this->nova_altura )
          {
               return false;
          }
          // se passou altura
          elseif ( !$this->nova_largura )
          {
               $this->nova_largura = $this->largura / ( $this->altura/$this->nova_altura );
          }
          // se passou largura
          elseif ( !$this->nova_altura )
          {
               $this->nova_altura = $this->altura / ( $this->largura/$this->nova_largura );
          }
          // redimensiona de acordo com tipo
          switch( $tipo )
          {
               case 'crop':
                    $this->redimensionaCrop();
                    break;
               case 'preenchimento':
                    $this->redimensionaPreenchimento();
                    break;
               case 'proporcional': 
                   // modo proporcional sem preenchimento adicionado por Fernando VR (goo.gl/iDtmP)
                    $this->redimensionaProporcional();
                    break;		
               default:
                    $this->redimensionaSimples();
                    break;
          }
          // atualiza dimensoes da imagem
          $this->altura     = $this->nova_altura;
          $this->largura     = $this->nova_largura;
          return $this;
     } // fim redimensiona
     /**
      * Redimensiona imagem, modo padrao, sem crop ou preenchimento
      * (distorcendo caso tenha passado ambos altura e largura)
      * @return void
      **/
     private function redimensionaSimples()
     {
          // cria imagem de destino tempororia
          $this->img_temp = imagecreatetruecolor( $this->nova_largura, $this->nova_altura );
          // adiciona cor de fundo  nova imagem
          $this->preencheImagem();
          imagecopyresampled( $this->img_temp, $this->img, 0, 0, 0, 0, $this->nova_largura, $this->nova_altura, $this->largura, $this->altura );
          $this->img     = $this->img_temp;
     } // fim redimensiona()
     /**
      * Adiciona cor de fundo a imagem
      * @return void
      **/
     private function preencheImagem()
     {     
          $cor_fundo = imagecolorallocate($this->img_temp, $this->rgb[0], $this->rgb[1], $this->rgb[2]);
          if ($this->transparencia){          	          	
          	imagealphablending($this->img_temp, false);
          	imagesavealpha($this->img_temp, true);
          	$cor_fundo = imagecolorallocatealpha($this->img_temp, $this->rgb[0], $this->rgb[1], $this->rgb[2], 100);
          }
          imagefill( $this->img_temp, 0, 0, $cor_fundo);
     } // fim preencheImagem
     /**
      * Redimensiona imagem sem cropar, proporcionalmente,
      * preenchendo espao vazio com cor rgb especificada
      * @return void
      **/
     private function redimensionaPreenchimento()
     {
          // cria imagem de destino temporria
          $this->img_temp = imagecreatetruecolor( $this->nova_largura, $this->nova_altura );
          // adiciona cor de fundo  nova imagem
          $this->preencheImagem();
          // salva variveis para centralizao
          $dif_x = $dif_w = $this->nova_largura;
          $dif_y = $dif_h = $this->nova_altura;
          /**
      		 * Verifica altura e largura
      		 * Calculo corrigido por Gilton Guma <http://www.gsguma.com.br/>
      		 */
          if ( ($this->largura / $this->nova_largura ) > ( $this->altura / $this->nova_altura ) )
          {
              $fator = $this->largura / $this->nova_largura;
          } else {
              $fator = $this->altura / $this->nova_altura;
          }
          $dif_w = $this->largura / $fator;
          $dif_h = $this->altura  / $fator;
          // copia com o novo tamanho, centralizando
          $dif_x = ( $dif_x - $dif_w ) / 2;
          $dif_y = ( $dif_y - $dif_h ) / 2;
          imagecopyresampled( $this->img_temp, $this->img, $dif_x, $dif_y, 0, 0, $dif_w, $dif_h, $this->largura, $this->altura );
          $this->img     = $this->img_temp;
     } // fim redimensionaPreenchimento()
     /**
      * Redimensiona imagem sem cropar, proporcionalmente e sem preenchimento.
      * Modo proporcional adicionado por Fernando VR ( http://goo.gl/iDtmP )
      * @return void
      **/
     private function redimensionaProporcional()
     {
	   /**
           * Verifica altura e largura proporcional.
           **/
		   $ratio_orig = $this->largura/$this->altura;
			if ($this->nova_largura/$this->nova_altura > $ratio_orig) {
			   $dif_w = $this->nova_altura*$ratio_orig;
			   $dif_h = $this->nova_altura;
			} else {
				$dif_w = $this->nova_largura;
			   $dif_h = $this->nova_largura/$ratio_orig;
			}
          // cria imagem de destino temporria
          $this->img_temp = imagecreatetruecolor( $dif_w, $dif_h );
          // Resample
	  imagecopyresampled($this->img_temp, $this->img, 0, 0, 0, 0, $dif_w, $dif_h, $this->largura, $this->altura);
          $this->img   = $this->img_temp;
     } // fim redimensionaProporcional()
     /**
      * Calcula a posio do crop
      * Os ndices 0 e 1 correspondem  posio x e y do crop na imagem
      * Os ndices 2 e 3 correspondem ao tamanho do crop
      * @return void
      **/
     private function calculaPosicaoCrop()
     {
          // mdia altura/largura
          $hm     = $this->altura / $this->nova_altura;
          $wm     = $this->largura / $this->nova_largura;
          // 50% para clculo do crop
          $h_height = $this->nova_altura / 2;
          $h_width  = $this->nova_largura / 2;
          // calcula novas largura e altura
          if( !is_array( $this->posicao_crop ) )
          {
               if ( $wm > $hm )
               {
                    $this->posicao_crop[2]     = $this->largura / $hm;
                    $this->posicao_crop[3]     = $this->nova_altura;
                    $this->posicao_crop[0]     = ( $this->posicao_crop[2] / 2 ) - $h_width;
                    $this->posicao_crop[1]     = 0;
               }
               // largura <= altura
               elseif ( ( $wm <= $hm ) )
               {
                    $this->posicao_crop[2]     = $this->nova_largura;
                    $this->posicao_crop[3]     = $this->altura / $wm;
                    $this->posicao_crop[0]     = 0;
                    $this->posicao_crop[1]     = ( $this->posicao_crop[3] / 2 ) - $h_height;
               }
          }
     } // fim calculaPosicaoCrop
     /**
      * Redimensiona imagem, cropando para encaixar no novo tamanho, sem sobras
      * baseado no script original de Noah Winecoff
      * http://www.findmotive.com/2006/12/13/php-crop-image/
      * atualizado para receber o posicionamento X e Y do crop na imagem
      * @return void
      **/
     private function redimensionaCrop()
     {
          // calcula posicionamento do crop
          $this->calculaPosicaoCrop();
          // cria imagem de destino temporria
          $this->img_temp = imagecreatetruecolor( $this->nova_largura, $this->nova_altura );
          // adiciona cor de fundo  nova imagem
          $this->preencheImagem();
          imagecopyresampled( $this->img_temp, $this->img, -$this->posicao_crop[0], -$this->posicao_crop[1], 0, 0, $this->posicao_crop[2], $this->posicao_crop[3], $this->largura, $this->altura );
          $this->img     = $this->img_temp;
     } // fim redimensionaCrop
     /**
      * flipa/inverte imagem
      * baseado no script original de Noah Winecoff
      * http://www.php.net/manual/en/ref.image.php#62029
      * @param String $tipo tipo de espelhamento: h - horizontal, v - vertical
      * @return Object instncia atual do objeto, para mtodos encadeados
      **/
     public function flip( $tipo = 'h' )
     {
          $w = imagesx( $this->img );
          $h = imagesy( $this->img );
          $this->img_temp = imagecreatetruecolor( $w, $h );
          // vertical
          if ( 'v' == $tipo )
          {
               for ( $y = 0; $y < $h; $y++ )
               {
                    imagecopy( $this->img_temp, $this->img, 0, $y, 0, $h - $y - 1, $w, 1 );
               }
          }
          // horizontal
          elseif ( 'h' == $tipo )
          {
               for ( $x = 0; $x < $w; $x++ )
               {
                    imagecopy( $this->img_temp, $this->img, $x, 0, $w - $x - 1, 0, 1, $h );
               }
          }
          $this->img     = $this->img_temp;
          return $this;
     } // fim flip
     /**
      * gira imagem
      * @param Int $graus grau para giro
      * @return Object instncia atual do objeto, para mtodos encadeados
      **/
     public function gira( $graus )
     {
          $cor_fundo     = imagecolorallocate( $this->img, $this->rgb[0], $this->rgb[1], $this->rgb[2] );
          $this->img     = imagerotate( $this->img, $graus, $cor_fundo );
          imagealphablending( $this->img, true );
          imagesavealpha( $this->img, true );
          $this->largura = imagesx( $this->img );
          $this->altura = imagesx( $this->img );
          return $this;
     } // fim girar
     /**
      * adiciona texto  imagem
      * @param String $texto texto a ser inserido
      * @param Int $tamanho tamanho da fonte
      *            Ver: http://br2.php.net/imagestring
      * @param Int $x posio x do texto na imagem
      * @param Int $y posio y do texto na imagem
      * @param Array/String $cor_fundo array com cores RGB ou string com cor hexadecimal
      * @param Boolean $truetype true para utilizar fonte truetype, false para fonte do sistema
      * @param String $fonte nome da fonte truetype a ser utilizada
      * @return void
      **/
     public function legenda( $texto, $tamanho = 5, $x = 0, $y = 0, $cor_fundo = '', $truetype = false, $fonte = '' )
     {
          $cor_texto = imagecolorallocate( $this->img, $this->rgb[0], $this->rgb[1], $this->rgb[2] );
          /**
           * Define tamanho da legenda para posies fixas e fundo da legenda
           **/
          if( $truetype  === true )
          {
               $dimensoes_texto     = imagettfbbox( $tamanho, 0, $fonte, $texto );
               $largura_texto          = $dimensoes_texto[4];
               $altura_texto          = $tamanho;
          }
          else
          {
               if( $tamanho > 5 ) $tamanho = 5;
               $largura_texto     = imagefontwidth( $tamanho ) * strlen( $texto );
               $altura_texto     = imagefontheight( $tamanho );
          }
          if( is_string( $x ) && is_string( $y ) )
          {
               list( $x, $y ) = $this->calculaPosicaoLegenda( $x . '_' . $y, $largura_texto, $altura_texto );
          }
          /**
           * Cria uma nova imagem para usar de fundo da legenda
           **/
          if( $cor_fundo )
          {
               if( is_array( $cor_fundo ) )
               {
                    $this->rgb = $cor_fundo;
               }
               elseif( strlen( $cor_fundo ) > 3 )
               {
                    $this->hexa( $cor_fundo );
               }
               $this->img_temp = imagecreatetruecolor( $largura_texto, $altura_texto );
               $cor_fundo = imagecolorallocate( $this->img_temp, $this->rgb[0], $this->rgb[1], $this->rgb[2] );
               imagefill( $this->img_temp, 0, 0, $cor_fundo );
               imagecopy( $this->img, $this->img_temp, $x, $y, 0, 0, $largura_texto, $altura_texto );
          }
          // truetype ou fonte do sistema?
          if ( $truetype === true )
          {
               $y = $y + $tamanho;
               imagettftext( $this->img, $tamanho, 0, $x, $y, $cor_texto, $fonte, $texto );
          }
          else
          {
               imagestring( $this->img, $tamanho, $x, $y, $texto, $cor_texto );
          }
          return $this;
     } // fim legenda
    /**
     * Calcula a posio da legenda de acordo com string passada via parmetro
     *
     * @param String $posicao valores pr-definidos (topo_esquerda, meio_centro etc.)
     * @param Integer $largura largura da imagem
     * @param Integer $altura altura da imagem
     * @return void
     **/
     private function calculaPosicaoLegenda( $posicao, $largura, $altura )
     {
          // define X e Y para posicionamento
          switch( $posicao )
          {
               case 'topo_esquerda':
                    $x = 0;
                    $y = 0;
                    break;
               case 'topo_centro':
                    $x = ( $this->largura - $largura ) / 2;
                    $y = 0;
                    break;
               case 'topo_direita':
                    $x = $this->largura - $largura;
                    $y = 0;
                    break;
               case 'meio_esquerda':
                    $x = 0;
                    $y = ( $this->altura / 2 ) - ( $altura / 2 );
                    break;
               case 'meio_centro':
                    $x = ( $this->largura - $largura ) / 2;
                    $y = ( $this->altura - $altura ) / 2 ;
                    break;
               case 'meio_direita':
                    $x = $this->largura - $largura;
                    $y = ( $this->altura / 2) - ( $altura / 2 );
                    break;
               case 'baixo_esquerda':
                    $x = 0;
                    $y = $this->altura - $altura;
                    break;
               case 'baixo_centro':
                    $x = ( $this->largura - $largura ) / 2;
                    $y = $this->altura - $altura;
                    break;
               case 'baixo_direita':
                    $x = $this->largura - $largura;
                    $y = $this->altura - $altura;
                    break;
               default:
                    return false;
                    break;
          } // end switch posicao
          return array( $x, $y );
     } // fim calculaPosicaoLegenda
     /**
      * adiciona imagem de marca d'gua
      * @param String $imagem caminho da imagem de marca d'gua
      * @param Int/String $x posio x da marca na imagem ou constante para marcaFixa()
      * @param Int/Sring $y posio y da marca na imagem ou constante para marcaFixa()
      * @return Boolean true/false dependendo do resultado da operao
      * @param Int $alfa valor para transparncia (0-100)
      *                 -> se utilizar alfa, a funo imagecopymerge no preserva
      *                 -> o alfa nativo do PNG
      * @return Object instncia atual do objeto, para mtodos encadeados
      **/
     public function marca( $imagem, $x = 0, $y = 0, $alfa = 100 )
     {
          // cria imagem temporria para merge
          if ( $imagem ) {
               if( is_string( $x ) && is_string( $y ) )
               {
                    return $this->marcaFixa( $imagem, $x . '_' . $y, $alfa );
               }
               $pathinfo = pathinfo( $imagem );
               switch( strtolower( $pathinfo['extension'] ) )
               {
                    case 'jpg':
                    case 'jpeg':
                         $marcadagua = imagecreatefromjpeg( $imagem );
                         break;
                    case 'png':
                         $marcadagua = imagecreatefrompng( $imagem );
                         break;
                    case 'gif':
                         $marcadagua = imagecreatefromgif( $imagem );
                         break;
                    case 'bmp':
                         $marcadagua = imagecreatefrombmp( $imagem );
                         break;
                    default:
                         trigger_error( 'Arquivo de marca d\'gua invlido.', E_USER_ERROR );
                         return false;
               }
          }
          else
          {
               return false;
          }
          // dimenses
          $marca_w     = imagesx( $marcadagua );
          $marca_h     = imagesy( $marcadagua );
          // retorna imagens com marca d'gua
          if ( is_numeric( $alfa ) && ( ( $alfa > 0 ) && ( $alfa < 100 ) ) ) {
               imagecopymerge( $this->img, $marcadagua, $x, $y, 0, 0, $marca_w, $marca_h, $alfa );
          } else {
               imagecopy( $this->img, $marcadagua, $x, $y, 0, 0, $marca_w, $marca_h );
          }
          return $this;
     } // fim marca
     /**
      * adiciona imagem de marca d'gua, com valores fixos
      * ex: topo_esquerda, topo_direita etc.
      * Implementao original por Giolvani <inavloig@gmail.com>
      * @param String $imagem caminho da imagem de marca d'gua
      * @param String $posicao posio/orientao fixa da marca d'gua
      *       [topo, meio, baixo] + [esquerda, centro, direita]
      * @param Int $alfa valor para transparncia (0-100)
      * @return void
      **/
     public function marcaFixa( $imagem, $posicao, $alfa = 100 )
     {     	     
          // dimenses da marca d'gua
          list( $marca_w, $marca_h ) = getimagesize( $imagem );
          // define X e Y para posicionamento
          switch( $posicao )
          {
               case 'topo_esquerda':
                    $x = 0;
                    $y = 0;
                    break;
               case 'topo_centro':
                    $x = ( $this->largura - $marca_w ) / 2;
                    $y = 0;
                    break;
               case 'topo_direita':
                    $x = $this->largura - $marca_w;
                    $y = 0;
                    break;
               case 'meio_esquerda':
                    $x = 0;
                    $y = ( $this->altura / 2 ) - ( $marca_h / 2 );
                    break;
               case 'meio_centro':
                    $x = ( $this->largura - $marca_w ) / 2;
                    $y = ( $this->altura / 2 ) - ( $marca_h / 2 );
                    break;
               case 'meio_direita':
                    $x = $this->largura - $marca_w;
                    $y = ( $this->altura / 2) - ( $marca_h / 2 );
                    break;
               case 'baixo_esquerda':
                    $x = 0;
                    $y = $this->altura - $marca_h;
                    break;
               case 'baixo_centro':
                    $x = ( $this->largura - $marca_w ) / 2;
                    $y = $this->altura - $marca_h;
                    break;
               case 'baixo_direita':
                    //$x = $this->largura - $marca_w;
                    //$y = $this->altura - $marca_h;
               		$x = ($this->largura - $marca_w) - 5;
               		$y = ($this->altura - $marca_h) - 5;
                    break;
               default:
                    return false;
                    break;
          } // end switch posicao
          // cria marca
          $this->marca( $imagem, $x, $y, $alfa );
          return $this;
     } // fim marcaFixa
     public function unir($primeira_imagem,$segunda_imagem,$posicao,$destino){
     	// dimensoes da primeira imagem
     	list( $primeira_imagem_w, $primeira_imagem_h ) = getimagesize( $primeira_imagem );
     	list( $segunda_imagem_w, $segunda_imagem_h ) = getimagesize( $segunda_imagem );
     	//Pega a maior altura e a maior largura entre as imagens
     	if($primeira_imagem_w > $primeira_imagem_w){
     		$maiorw = $primeira_imagem_w;
     	} else {
     		$maiorw = $segunda_imagem_w;
     	}
     	if($primeira_imagem_h > $primeira_imagem_h){
     		$maiorh = $primeira_imagem_h;
     	} else {
     		$maiorh = $segunda_imagem_h;
     	}
     	$w = $primeira_imagem_w + $segunda_imagem_w;
     	$h = $primeira_imagem_h + $segunda_imagem_h;
     	// define X e Y para posicionamento
     	switch( $posicao )
     	{
     		case 'lado-a-lado':     			
     			$x = 0;
     			$y = 0;
     			$x2 = $primeira_imagem_w;
     			$y2 = 0;
     			$h = $maiorh;
     			break;
     		case 'abaixo':     			
     			$x = 0;
     			$y = 0;
     			$x2 = 0;
     			$y2 = $primeira_imagem_h;
     			$w = $maiorw;
     			break;
     		default:
     			return false;
     			break;
     	}
     	// cria uniao
     	$this->juntar($primeira_imagem,$x,$y,$segunda_imagem,$x2,$y2,$w,$h,$destino);
     	return $this;
     }
     public function juntar($primeira_imagem,$x,$y,$segunda_imagem,$x2,$y2,$w,$h,$destino){
     	// Cria uma imagem nova com as maiores medidas entre as duas imagens
     	$imgDest = imagecreatetruecolor($w, $h);
     	// Pinta o fundo de branco
     	imageFill($imgDest, 0, 0, ImageColorAllocate($imgDest, 255, 255, 255));
     	// Le a primeira imagem     	
     	$pathinfo = pathinfo( $primeira_imagem );
     	switch( strtolower( $pathinfo['extension'] ) )
     	{
     		case 'jpg':
     		case 'jpeg':
     			$first = imagecreatefromjpeg( $primeira_imagem );
     			break;
     		case 'png':
     			$first = imagecreatefrompng( $primeira_imagem );
     			break;
     		case 'gif':
     			$first = imagecreatefromgif( $primeira_imagem );
     			break;
     		case 'bmp':
     			$first = imagecreatefrombmp( $primeira_imagem );
     			break;
     		default:
     			trigger_error( 'Primeira imagem inv�lida.', E_USER_ERROR );
     			return false;
     	}
     	// Le a segunda imagem     	
     	$pathinfo = pathinfo( $segunda_imagem );
     	switch( strtolower( $pathinfo['extension'] ) )
     	{
     		case 'jpg':
     		case 'jpeg':
     			$last = imagecreatefromjpeg( $segunda_imagem );
     			break;
     		case 'png':
     			$last = imagecreatefrompng( $segunda_imagem );
     			break;
     		case 'gif':
     			$last = imagecreatefromgif( $segunda_imagem );
     			break;
     		case 'bmp':
     			$last = imagecreatefrombmp( $segunda_imagem );
     			break;
     		default:
     			trigger_error( 'Segunda imagem inválida.', E_USER_ERROR );
     			return false;
     	}
     	// Coloca as imagens no destino
     	imageCopy($imgDest, $first, 0, 0, 0, 0, $w, $h);
     	imageCopy($imgDest, $last, $x2, $y2, 0, 0, $w, $h);
     	imagejpeg($imgDest, $destino);
     	//apaga a imagem auxiliar
     	if (file_exists ( $segunda_imagem )) {
     		unlink ( $segunda_imagem );
     	}
     	return $this;
     }
    /**
      * Aplica filtros avanados como brilho, contraste, pixelate, blur
      * Requer o GD compilado com a funo imagefilter()
      * http://br.php.net/imagefilter
      * @param String $filtro constante/nome do filtro
      * @param Integer $quantidade nmero de vezes que o filtro deve ser aplicado
      *            utilizado em blur, edge, emboss, pixel e rascunho
      * @param $arg1, $arg2 e $arg3 - ver manual da funo imagefilter
      * @return Object instncia atual do objeto, para mtodos encadeados
     **/
    public function filtra( $filtro, $quantidade = 1, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL )
    {
         switch( $filtro )
         {
             case 'blur':
                if( is_numeric( $quantidade ) && $quantidade > 1 )
                {
                    for( $i = 1; $i <= $quantidade; $i++ )
                    {
                        imagefilter( $this->img, IMG_FILTER_GAUSSIAN_BLUR );
                    }
                }
                else
                {
                    imagefilter( $this->img, IMG_FILTER_GAUSSIAN_BLUR );
                }
                break;
            case 'blur2':
                if( is_numeric( $quantidade ) && $quantidade > 1 )
                {
                    for( $i = 1; $i <= $quantidade; $i++ )
                    {
                        imagefilter( $this->img, IMG_FILTER_SELECTIVE_BLUR );
                    }
                }
                else
                {
                    imagefilter( $this->img, IMG_FILTER_SELECTIVE_BLUR );
                }
                break;
            case 'brilho':
                imagefilter( $this->img, IMG_FILTER_BRIGHTNESS, $arg1 );
                break;
            case 'cinzas':
                imagefilter( $this->img, IMG_FILTER_GRAYSCALE );
                break;
            case 'colorir':
                imagefilter( $this->img, IMG_FILTER_COLORIZE, $arg1, $arg2, $arg3, $arg4 );
                break;
            case 'contraste':
                imagefilter( $this->img, IMG_FILTER_CONTRAST, $arg1 );
                break;
            case 'edge':
                if( is_numeric( $quantidade ) && $quantidade > 1 )
                {
                    for( $i = 1; $i <= $quantidade; $i++ )
                    {
                        imagefilter( $this->img, IMG_FILTER_EDGEDETECT );
                    }
                }
                else
                {
                    imagefilter( $this->img, IMG_FILTER_EDGEDETECT );
                }
                break;
            case 'emboss':
                if( is_numeric( $quantidade ) && $quantidade > 1 )
                {
                    for( $i = 1; $i <= $quantidade; $i++ )
                    {
                        imagefilter( $this->img, IMG_FILTER_EMBOSS );
                    }
                }
                else
                {
                    imagefilter( $this->img, IMG_FILTER_EMBOSS );
                }
                break;
            case 'negativo':
                imagefilter( $this->img, IMG_FILTER_NEGATE );
                break;
            case 'ruido':
                if( is_numeric( $quantidade ) && $quantidade > 1 )
                {
                    for( $i = 1; $i <= $quantidade; $i++ )
                    {
                        imagefilter( $this->img, IMG_FILTER_MEAN_REMOVAL );
                    }
                }
                else
                {
                    imagefilter( $this->img, IMG_FILTER_MEAN_REMOVAL );
                }
                break;
            case 'suave':
                if( is_numeric( $quantidade ) && $quantidade > 1 )
                {
                    for( $i = 1; $i <= $quantidade; $i++ )
                    {
                        imagefilter( $this->img, IMG_FILTER_SMOOTH, $arg1 );
                    }
                }
                else
                {
                    imagefilter( $this->img, IMG_FILTER_SMOOTH, $arg1 );
                }
                break;
            // SOMENTE 5.3 ou superior
            case 'pixel':
                if( is_numeric( $quantidade ) && $quantidade > 1 )
                {
                    for( $i = 1; $i <= $quantidade; $i++ )
                    {
                        imagefilter( $this->img, IMG_FILTER_PIXELATE, $arg1, $arg2 );
                    }
                }
                else
                {
                    imagefilter( $this->img, IMG_FILTER_PIXELATE, $arg1, $arg2 );
                }
                break;
            default:
                break;
         }
          return $this;
    } // fim filtrar
    /**  
    Adiciona o melhor filtro para as imagens o sharpen | Jefferson Oliveira 
    Usa GD image objects 
    **/
   function imagesharpen() {
        $qualidade = array(
            array(-1, -1, -1),
            array(-1, 16, -1),
            array(-1, -1, -1),
        );
        $divisao = array_sum(array_map('array_sum', $qualidade));
        $offset = 0; 
        imageconvolution($this->img, $qualidade, $divisao, $offset);
        return $this;
    }	
     /**
      * retorna sada para tela ou arquivo
      * @param String $destino caminho e nome do arquivo a serem criados
      * @param Int $qualidade qualidade da imagem no caso de JPEG (0-100)
      * @return void
      **/
     public function grava( $destino='', $qualidade = 100 )
     {
          // dados do arquivo de destino
          if ( $destino )
          {
               $pathinfo               = pathinfo( $destino );
               $dir_destino          = $pathinfo['dirname'];
               $extensao_destino     = strtolower( $pathinfo['extension'] );
               // valida diretrio
               if ( !is_dir( $dir_destino ) )
               {
                    trigger_error( 'Diretrio de destino invlido ou inexistente', E_USER_ERROR );
               }
          }
          if ( !isset( $extensao_destino ) )
          {
               $extensao_destino = $this->extensao;
          }
          switch( $extensao_destino )
          {
               case 'jpg':
               case 'jpeg':
               case 'bmp':
                    if ( $destino )
                    {
                         imagejpeg( $this->img, $destino, $qualidade );
                    }
                    else
                    {
                         header( "Content-type: image/jpeg" );
                         imagejpeg( $this->img, NULL, $qualidade );
                         imagedestroy( $this->img );
                         exit;
                    }
                    break;
               case 'png':
                    if ( $destino )
                    {
                         imagepng( $this->img, $destino );
                    }
                    else
                    {
                         header( "Content-type: image/png" );
                         imagepng( $this->img );
                         imagedestroy( $this->img );
                         exit;
                    }
                    break;
               case 'gif':
                    if ( $destino )
                    {
                         imagegif( $this->img, $destino );
                    }
                    else
                    {
                         header( "Content-type: image/gif" );
                         imagegif( $this->img );
                         imagedestroy( $this->img );
                         exit;
                    }
                    break;
               default:
                    return false;
                    break;
          }
     } // fim grava
     public function upload($file,$caminho) {
     	if (!file_exists($caminho)) {                
     		mkdir($caminho, 0777);     		
     	}
     	if (!move_uploaded_file($file['tmp_name'], $caminho.$file['name'])) {
     		trigger_error( 'Arquivo no enviado', E_USER_ERROR );
     	} else { 
     		mkdir($file['tmp_name'], 0777);
     		//chown($file['tmp_name'], "agenciawiv");
     		return $caminho;
     	}
     }//fim do upload
     public function getMime($filename) {
     	$extension  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
     	//preg_match("/\.(.*?)$/", $filename, $m);    # Get File extension for a better match
     	switch ($extension) {
     		case "js": return "application/javascript";
     		case "json": return "application/json";
     		case "jpg": case "jpeg": case "jpe": return "image/jpg";
     		case "png": case "gif": case "bmp": return "image/" . $extension;
     		case "pdf": case "xml": case "zip": return "application/" . $extension;
     		case "css": return "text/css";
     		case "exe": return "application/octet-stream";
     		case "doc": return "application/msword";
     		case "xls": return "application/vnd.ms-excel";
     		case "ppt": return "application/vnd.ms-powerpoint";
     		case "html": case "htm": case "php": return "text/html";
     		default:
     			if (function_exists("mime_content_type")) { # if mime_content_type exists use it.
     				$m = mime_content_type($filename);
     			} else if (function_exists("")) {    # if Pecl installed use it
     				$finfo = finfo_open(FILEINFO_MIME);
     				$m = finfo_file($finfo, $filename);
     				finfo_close($finfo);
     			} else {    # if nothing left try shell
     				if (strstr($_SERVER[HTTP_USER_AGENT], "Windows")) { # Nothing to do on windows
     				return ""; # Blank mime display most files correctly especially images.
     			}
     			if (strstr($_SERVER[HTTP_USER_AGENT], "Macintosh")) { # Correct output on macs
     				$m = trim(exec('file -b --mime ' . escapeshellarg($filename)));
     			} else {    # Regular unix systems
     				$m = trim(exec('file -bi ' . escapeshellarg($filename)));
     			}
     			}
     			$m = split(";", $m);
     			return trim($m[0]);
     	}
     }
	public function Imagem($fileName,$width=null,$height=null,$efeito=null,$arquivoSaida=null) {
     	$efeito = (empty($efeito))?"simples":$efeito;
     	
     	$file = pathinfo($fileName);     	
     	$imgTmp = $file['dirname']."/".$file['filename']."-{$width}x{$height}".".".$file['extension'];
     	
     	//Se o arquivo já existir eu retorno o mesmo
     	if(file_exists(getcwd().$imgTmp)){
     	    return $imgTmp;     	         	            
     	} else {
     	    $filePath = getcwd() . DIRECTORY_SEPARATOR . $fileName;
     	    if(!file_exists($filePath)){
     	     return;    
     	    }
     	    $imnfo = getimagesize($filePath);
     	    
     	    $larguraImagem = $imnfo["0"];
     	    $alturaImagem = $imnfo["1"];
     	    if($alturaImagem > $larguraImagem && $efeito == "simples"){
     	        $efeito = "crop";
     	    }
     	    
     	    $aux = explode(".", $fileName);
     	    $ext = $aux[1];
     	    $transparencia =($ext=="png")?1:0;
     	    if(file_exists($filePath)):
     	    //Redimensionando imagem principal
     	    $this->carrega($filePath);
     	    $this->redimensiona($nova_largura = $width, $nova_altura = $height, $tipo = $efeito, $transparencia);
     	    if(!empty($arquivoSaida)){
     	        $fileName = $arquivoSaida;
     	        $filePath = getcwd() . DIRECTORY_SEPARATOR . $arquivoSaida;
     	        $pathInfo = pathinfo($filePath);
     	        if(!file_exists($pathInfo["dirname"])){
     	            mkdir($pathInfo["dirname"],0777);
     	        }
     	    } else {
     	        $arquivoSaida = $file['dirname']."/".$file['filename']."-{$width}x{$height}".".".$file['extension'];
     	        $fileName = $arquivoSaida;
     	        $filePath = getcwd() . DIRECTORY_SEPARATOR . $arquivoSaida;
     	    }
     	    $this->grava($filePath, 100);
     	    endif;
     	    
     	    return $fileName;
     	}
     	 	
     }
} // fim da classe
//------------------------------------------------------------------------------
// suporte para a manipulao de arquivos BMP
/*********************************************/
/* Function: ImageCreateFromBMP              */
/* Author:   DHKold                          */
/* Contact:  admin@dhkold.com                */
/* Date:     The 15th of June 2005           */
/* Version:  2.0B                            */
/*********************************************/
function imagecreatefrombmp($filename) {
 //Ouverture du fichier en mode binaire
   if (! $f1 = fopen($filename,"rb")) return FALSE;
 //1 : Chargement des ent?tes FICHIER
   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
   if ($FILE['file_type'] != 19778) return FALSE;
 //2 : Chargement des ent?tes BMP
   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                     '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                     '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] = 4-(4*$BMP['decal']);
   if ($BMP['decal'] == 4) $BMP['decal'] = 0;
 //3 : Chargement des couleurs de la palette
   $PALETTE = array();
   if ($BMP['colors'] < 16777216)
   {
     $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
   }
 //4 : Cr?ation de l'image
   $IMG = fread($f1,$BMP['size_bitmap']);
   $VIDE = chr(0);
   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
   $P = 0;
   $Y = $BMP['height']-1;
   while ($Y >= 0)
   {
     $X=0;
     while ($X < $BMP['width'])
     {
      if ($BMP['bits_per_pixel'] == 24)
          $COLOR = @unpack("V",substr($IMG,$P,3).$VIDE);
      elseif ($BMP['bits_per_pixel'] == 16)
      {
          $COLOR = @unpack("n",substr($IMG,$P,2));
          $COLOR[1] = $PALETTE[$COLOR[1]+1];
      }
      elseif ($BMP['bits_per_pixel'] == 8)
      {
          $COLOR = @unpack("n",$VIDE.substr($IMG,$P,1));
          $COLOR[1] = $PALETTE[$COLOR[1]+1];
      }
      elseif ($BMP['bits_per_pixel'] == 4)
      {
          $COLOR = @unpack("n",$VIDE.substr($IMG,floor($P),1));
          if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
          $COLOR[1] = $PALETTE[$COLOR[1]+1];
      }
      elseif ($BMP['bits_per_pixel'] == 1)
      {
          $COLOR = @unpack("n",$VIDE.substr($IMG,floor($P),1));
          if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]          >>7;
          elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
          elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
          elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
          elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
          elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
          elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
          elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
          $COLOR[1] = $PALETTE[$COLOR[1]+1];
      }
      else
          return FALSE;
      imagesetpixel($res,$X,$Y,$COLOR[1]);
      $X++;
      $P += $BMP['bytes_per_pixel'];
     }
     $Y--;
     $P+=$BMP['decal'];
   }
 //Fermeture du fichier
   fclose($f1);
 return $res;
} // fim function image from BMP