<?php
/*
 * @version 1.0
 * @prestashopVersion 1.4.2
 *
 */

require_once(_PS_MODULE_DIR_.'googleshopping'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'myTools.php');
require_once(_PS_MODULE_DIR_.'googleshopping'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'html2text.inc.php');
require_once(_PS_MODULE_DIR_.'googleshopping'.DIRECTORY_SEPARATOR.'functions.php');				// fonctions

class GoogleShopping extends Module
{
	function __construct()
	{
		$this->name = 'googleshopping';
		$this->tab = 'market_place';
		$this->version = '1.0';
		$this->autor ='Informática Cano';

		parent::__construct();

		$config = Configuration::getMultiple(array('IGW_DOMAIN', 'IGW_LICNUM'));
		if (isset($config['IGW_DOMAIN']))
			$this->_domain = $config['IGW_DOMAIN'];
		if (isset($config['IGW_LICNUM']))
			$this->_licnum = $config['IGW_LICNUM'];

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Google Shopping');
		$this->description = $this->l('Exporta tus productos a Google Shopping.');
	}

	function install()
	{
		if(!parent::install())
		{
			return false;
		}
		return true;
	}


	public function uninstall()
	{
		Configuration::deleteByName('GS_PRODUCT_TYPE');
		Configuration::deleteByName('GS_SHIPPING');

		Configuration::deleteByName('IGW_DOMAIN');
		Configuration::deleteByName('IGW_LICNUM');

		return parent::uninstall();
	}

	public static function generateFile()
	{
		global $link;
		$path_parts = pathinfo(__FILE__);

		echo '<br>Idioma: '.$lang= Configuration::get("PS_LANG_DEFAULT");
		

		if (Configuration::get('GENERATE_FILE_IN_ROOT')):
    		$generate_file_path = "../../googleshopping.xml";
        else:
        	$generate_file_path = $path_parts["dirname"].'/googleshopping.xml';
        endif;
		echo '<br>Destino: '.$generate_file_path;
		//Google Shopping XML
		echo '<br>Se pone cabecera';
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
		$xml .= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0" encoding="UTF-8" >'."\n";
		$xml .= '<title>'.Configuration::get('PS_SHOP_NAME').'</title>'."\n";
		$xml .= '<link href="http://'.myTools::getHttpHost(false, true).__PS_BASE_URI__.'" rel="alternate" type="text/html"/>'."\n";
		$xml .= '<modified>'.date('Y-m-d').'T01:01:01Z</modified><author><name>'.Configuration::get('PS_SHOP_NAME').'</name></author>'."\n";

		$googleshoppingfile = fopen($generate_file_path,'w');

		fwrite($googleshoppingfile, $xml);

		$sql='SELECT p.id_product,name,id_category_default,link_rewrite,id_manufacturer,ean13,quantity,available_for_order,weight,on_sale FROM '._DB_PREFIX_.'product p '.
		' JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product '.
		' WHERE p.active = 1 and p.available_for_order=1 AND pl.id_lang='.$lang;

		echo '<br>Obteniendo productos';

		$products = Db::getInstance()->ExecuteS($sql);
		echo '<br>Sincronizando '.count($products).' productos <ul>';
		$site_base = __PS_BASE_URI__;		// préfix du site
		$url_site = myTools::getHttpHost(false, true);	// url du site base Serveur
		$url_site_base_prestashop = $url_site.$site_base;

		$title_limit = 70;
		$description_limit = 10000;

		foreach($products as $product)
		{
			//echo '<li>';
			$xml_googleshopping ='';
			$cat_link_rew = Category::getLinkRewrite($product['id_category_default'], intval($lang));
			$product_link = $link->getProductLink($product['id_product'] , $product['link_rewrite'], $cat_link_rew);
			//echo '|producto:'.$product['id_product'];
			$title_crop = $product['name'];
			if(strlen($product['name']) > $title_limit)
			{
				$title_crop = substr($title_crop, 0, ($title_limit-1));
				$title_crop = substr($title_crop, 0, strrpos($title_crop," "));
			}

			if(intval(Configuration::get('DESCRIPTION')) === intval(2))
			{
				$description_crop = $product['description'];
			} else {
				$description_crop = $product['description_short'];
			}
			$description_crop = myTools::f_convert_text("", $description_crop, false);

			if(strlen($description_crop) > $description_limit)
			{
				$description_crop = substr($description_crop, 0, ($description_limit-1));
				$description_crop = substr($description_crop, 0, strrpos($description_crop," "));
			}
			$xml_googleshopping .= '<entry>'."\n";
			$xml_googleshopping .= '<g:id>'.$product['id_product'].'</g:id>'."\n";
			$xml_googleshopping .= '<title>'.htmlspecialchars(ucfirst(mb_strtolower($title_crop,'UTF-8'))).'</title>'."\n";
			$xml_googleshopping .= '<link>'.$product_link.'</link>'."\n";
			$xml_googleshopping .= '<g:price>'.Product::getPriceStatic($product['id_product'],true,NULL,2).'</g:price>'."\n";
			$xml_googleshopping .= '<g:description>'.htmlspecialchars($description_crop, null, 'UTF-8', false).'</g:description>'."\n";
			$xml_googleshopping .= '<g:condition>new</g:condition>'."\n"; //condition = neuf, occasion, reconditionné OR new, used, refurbished
			$xml_googleshopping .= '<g:mpn>'.htmlspecialchars(Manufacturer::getNameById(intval($product['id_manufacturer'])), null, 'UTF-8', false).'</g:mpn>'."\n"; //ref fabricant
			$xml_googleshopping .= '<g:brand>'.htmlspecialchars(Manufacturer::getNameById(intval($product['id_manufacturer'])), null, 'UTF-8', false).'</g:brand>'."\n";	//marca
			$xml_googleshopping .= '<g:gtin>'.$product['ean13'].'</g:gtin>'."\n"; 
			
			// Pour chaque image
			$images = Image::getImages($lang, $product['id_product']);
			$nbimages=0;
			$image_type=Configuration::get('GS_IMAGE');
            if ($image_type=='') $image_type='large';
                    
         //echo '|image_type:'.$image_type;
			//echo '|imagenes:'.count($images); 
			foreach($images as $im)
			{
			  $image= $link->getImageLink($product['link_rewrite'], $product['id_product'] .'-'. $im['id_image'],$image_type);
			  //if (!strpos($url_site_base_prestashop,$image))
			   // $image = 'http://'.$url_site_base_prestashop.$image;
  			  $xml_googleshopping .= '<g:image_link>'.$image.'</g:image_link>'."\n";
  			  if (++$nbimages == 10) break;
			}


			if ($product['quantity'] > 0)
				$xml_googleshopping .= '<g:quantity>'.$product['quantity'].'</g:quantity>'."\n";
				
			if ($product['available_for_order'] == 1)
				$xml_googleshopping .= '<g:availability>in stock</g:availability>'."\n";			
			else		
				$xml_googleshopping .= '<g:availability>out of stock</g:availability>'."\n";


			// Catégorie
			if(Configuration::get('GS_PRODUCT_TYPE'))
			{
				$xml_googleshopping .= '<g:google_product_category>'.str_replace('>','&gt;',Configuration::get('GS_PRODUCT_TYPE')).'</g:google_product_category>'."\n";
				$xml_googleshopping .= '<g:product_type>'.str_replace('>','&gt;',Configuration::get('GS_PRODUCT_TYPE')).'</g:product_type>'."\n";
			}

			// Frais de port
			if(Configuration::get('GS_SHIPPING'))
			{
				$xml_googleshopping .= '<g:shipping>'."\n";
				$xml_googleshopping .= '<g:country>ES</g:country>'."\n";
				$xml_googleshopping .= '<g:service>Standard</g:service>'."\n";
				$xml_googleshopping .= '<g:price>'.Configuration::get('GS_SHIPPING').'</g:price>'."\n";
				$xml_googleshopping .= '</g:shipping>'."\n";
			}

			//Poids
			if($product['weight'] != '0')
			{
				$xml_googleshopping .= '<g:shipping_weight>'.$product['weight'].' kilograms</g:shipping_weight>'."\n";
			}

			// Offre spéciale
			if(Configuration::get('FEATURED_PRODUCT') == 1 && $product['on_sale'] != '0')
			{
				$xml_googleshopping .= '<g:featured_product>o</g:featured_product>'."\n";
			}


				$xml_googleshopping .= '</entry>'."\n";

			// Ecriture du produit dans l'XML googleshopping
			fwrite($googleshoppingfile, $xml_googleshopping);
			//echo '</li>';
		}
		echo '</ul>';
		$xml = '</feed>';
		fwrite($googleshoppingfile, $xml);
		fclose($googleshoppingfile);

		return true;
	}
}
?>