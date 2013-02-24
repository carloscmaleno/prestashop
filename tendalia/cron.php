<?php 
	//CONEXION Y CONSTANTES
	if (file_exists('config/config.inc.php'))
		include('config/config.inc.php'); 
	elseif(file_exists('../config/config.inc.php'))
		include('../config/config.inc.php'); 
	elseif(file_exists('../../config/config.inc.php'))
		include('../../config/config.inc.php'); 
	else
	{
		echo "No se encuentra el archivo config/config.inc.php";
		exit;
	}
	
	if (isset($_GET['d']) AND isset($_GET['c']))
	{	$d = $_GET['d'] * 1000; $c = $_GET['c'] * 1000;
	}else{	$d = 0; $c = 6000;}
		
	//VARIABLES DE CONFIGURACION
	$px = _DB_PREFIX_;	//mira el prefijo de las tablas
	$code = mysql_client_encoding();
	$xml = "<?xml version=\"tendalia3.3\" encoding=\"$code\" ?>\n";
	$xml.= "<DATOS>\n";
	$xml.= "<PS_VERSION>"._PS_VERSION_."</PS_VERSION>\n";
	$xml.= "<HTTP_HOST>".$_SERVER['HTTP_HOST']."</HTTP_HOST>\n"; 
	$xml.= "<THEME_PROD_DIR>"._THEME_PROD_DIR_."</THEME_PROD_DIR>\n";
	$xml.= "<PS_BASE_URI>".__PS_BASE_URI__."</PS_BASE_URI>\n";
	$xml.= "<PS_PROD_IMG>"._PS_PROD_IMG_."</PS_PROD_IMG>\n";
	$xml.= "<FECHA>".date("Y/m/d")."</FECHA>\n";

	//Idioma por defecto de la base de datos.
	$slc_lang = "SELECT value FROM ".$px."configuration where name='PS_LANG_DEFAULT';"; //elegir idioma de tienda.
	if ($result = Db::getInstance()->ExecuteS($slc_lang))
		$id_lang = $result[0]['value'];

	//Moneda por defecto de la base de datos.
	$moneda = '';
	$slc_currency = "SELECT cu.iso_code FROM ".$px."configuration c INNER JOIN ".$px."currency cu ON cu.id_currency = c.value WHERE c.name = 'PS_CURRENCY_DEFAULT'";
	if ($result = Db::getInstance()->ExecuteS($slc_currency))
		$moneda = $result[0]['iso_code'];
	$xml.= "<CURRENCY>".$moneda."</CURRENCY>\n";
	
	//Urls amigables activas
	$url_amigable = 0;
	$slc_url = "SELECT value FROM ".$px."configuration where name='PS_REWRITING_SETTINGS';";
	if ($result = Db::getInstance()->ExecuteS($slc_url))
		$url_amigable = $result[0]['value'];
	
	$xml.= "<PS_REWRITING_SETTINGS>".$url_amigable."</PS_REWRITING_SETTINGS>\n";
	$xml.= "</DATOS>\n";
	
	//mirar si el id_tax esta en la tabla de productos, o esta en una tabla aparte, cambia en las versiones.
	if (@mysql_num_rows(mysql_query("SHOW COLUMNS FROM ".$px."product LIKE 'id_tax' ;")) == 1 )	
		$vtax = "id_tax";
	else
		$vtax = "id_tax_rules_group";
	
	//mirar si las reducciones estan en la tabla productos o en tabla aparte.
	if (@mysql_num_rows(mysql_query("SHOW COLUMNS FROM ".$px."product LIKE 'reduction_price' ;")) == 1)
		$reduccion = ",prod.reduction_price, prod.reduction_percent";
	else
		$reduccion = "";
	
	$slc_prod = "SELECT prod.id_product, prodlang.name as nombre_producto, prod.price, prodlang.description_short, prodcat.id_category, prodmarca.name as nombre_marca, prodlang.link_rewrite, prodimage.id_image, prod.ean13, prodtasa.rate $reduccion
	FROM ".$px."product prod 
	LEFT JOIN ".$px."product_lang prodlang ON prod.id_product = prodlang.id_product 
	LEFT JOIN ".$px."category_product prodcat ON prod.id_product = prodcat.id_product
	LEFT JOIN ".$px."category_lang catlang ON prodcat.id_category = catlang.id_category 
	LEFT JOIN ".$px."supplier prodmarca ON prod.id_supplier = prodmarca.id_supplier
	LEFT JOIN ".$px."image prodimage ON prod.id_product = prodimage.id_product
	LEFT JOIN ".$px."tax prodtasa ON prod.".$vtax." = prodtasa.id_tax
	WHERE prod.active=1 AND prodlang.id_lang = $id_lang AND catlang.id_lang = $id_lang AND prodimage.cover =1
	GROUP BY prodcat.id_product ORDER BY prodcat.id_category DESC
	LIMIT $d, $c;";
	$product_list = Db::getInstance()->ExecuteS($slc_prod);

	if ($product_list)
	{
		$xml.= "<CATALOGO>\n";
		
		foreach ($product_list as $fila_producto)
		{
			$xml .= "<producto>\n";
			$xml .= "<id>".$fila_producto['id_product']."</id>\n";
			$xml .= "<nombre><![CDATA[".$fila_producto['nombre_producto']."]]></nombre>\n";
			$xml .= "<precio>".$fila_producto['price']."</precio>\n";
			if (isset($fila_producto['reduction_price'], $fila_producto['reduction_percent']))
			{
				$xml .= "<precio_red_pri>".$fila_producto['reduction_price']."</precio_red_pri>\n";
				$xml .= "<precio_red_per>".$fila_producto['reduction_percent']."</precio_red_per>\n";
			}
			$xml .= "<tax>".$fila_producto['rate']."</tax>\n";
			$xml .= "<descripcion><![CDATA[".strip_tags($fila_producto['description_short'])."]]></descripcion>\n";
			$xml .= "<id_categoria>".$fila_producto['id_category']."</id_categoria>\n";
			$xml .= "<marca><![CDATA[".$fila_producto['nombre_marca']."]]></marca>\n";
			$xml .= "<url_amigable>".$fila_producto['link_rewrite']."</url_amigable>\n";
			$xml .= "<id_imagen>".$fila_producto['id_image']."</id_imagen>\n";
			$xml .= "<ean13>".$fila_producto['ean13']."</ean13>\n";
			$xml .= "</producto>\n";
		}
		$xml.= "</CATALOGO>\n";
	}
	/******************************************************************************/
	//precios con descuento
	$sql_descuento = "SELECT id_product, reduction, reduction_type FROM ".$px."specific_price group by id_product;";
	if ($result_descuento = Db::getInstance()->ExecuteS($sql_descuento))
	{
		$xml.= "<DESCUENTOS>\n";
		foreach ($result_descuento as $fila_descuento)
		{
			$xml .= "<descuento>\n";
			$xml .= "<id>".$fila_descuento['id_product']."</id>\n";
			$xml .= "<reduccion>".$fila_descuento['reduction']."</reduccion>\n";
			$xml .= "<tipo>".$fila_descuento['reduction_type']."</tipo>\n";
			$xml .= "</descuento>\n";
		}
		$xml.= "</DESCUENTOS>\n";
	}
	
	/******************************************************************************/
	//Seleccionar datos de las tablas de categorias (ps_category, ps_category_lang)
	$slc_categorias = "SELECT c.id_category, c.id_parent, c.level_depth, cl.name FROM `".$px."category` c LEFT JOIN ".$px."category_lang cl ON cl.`id_category` =  c.`id_category` where cl.id_lang = $id_lang and c.active=1";
	if ($category_list = Db::getInstance()->ExecuteS($slc_categorias))
	{
		$xml.= "<CATEGORIAS>\n";
		foreach ($category_list as $fila_categoria)
		{
			$xml .= "<categoria>\n";
			$xml .= "<id>".$fila_categoria['id_category']."</id>\n";
			$xml .= "<id_padre>".$fila_categoria['id_parent']."</id_padre>\n";
			$xml .= "<nivel>".$fila_categoria['level_depth']."</nivel>\n";
			$xml .= "<nombre><![CDATA[".$fila_categoria['name']."]]></nombre>\n";
			$xml .= "</categoria>\n";
		}
		$xml.= "</CATEGORIAS>\n";
	}
	//echo $xml;
	
	$csv=fopen("products.csv","w");
	fwrite($csv,$xml);
	fclose($csv);
	echo 'Ok';
?>