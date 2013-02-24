<?php
	/*
 *
 * SyncG2k
 * Modulo privado
 * Licencia de uso:readme.txt
 *
 * @author InformÃ¡tica Cano Granada S.L.
 * @copyright InformÃ¡tica Cano Granada S.L.
 * @version 1.2
 * @prestashopVersion +1.4.2
 *
 */

require_once(_PS_MODULE_DIR_.'syncg2k'.DIRECTORY_SEPARATOR.'funciones.php');

class SyncG2k extends Module
{
	var $img_tmp;
	function __construct()
	{
		$this->name = 'syncg2k';
		$this->tab = 'pricing_promotion';
		$this->version = 1.2;
		$this->author= 'Informática Cano Granada S.L.';

		parent::__construct(); 

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Módulo de importación de productos');
		$this->description = $this->l('Mantiene la web sincronizada con los productos del G2k');
		$this->img_tmp=_PS_ROOT_DIR_.Configuration::get('SYNCG2K_IMG');
	}
	function installPatch(){
	
		Db::getInstance()->Execute('alter table '._DB_PREFIX_.'attribute add g2k varchar(5);');
		Db::getInstance()->Execute('alter table '._DB_PREFIX_.'attribute_group add g2k varchar(5);');
		Db::getInstance()->Execute('alter table '._DB_PREFIX_.'category add g2k varchar(5);');
		Db::getInstance()->Execute('alter table '._DB_PREFIX_.'manufacturer add g2k varchar(5);');

		Db::getInstance()->Execute('update '._DB_PREFIX_.'manufacturer set g2k=id_manufacturer ');	
		Db::getInstance()->Execute('update '._DB_PREFIX_.'category set g2k = id_category');	
		Db::getInstance()->Execute('update '._DB_PREFIX_.'attribute set g2k = id_attribute;');
		Db::getInstance()->Execute('update '._DB_PREFIX_.'attribute_group set g2k id_attribute_group');
		
		mkdir("/img/synckg2k");
		return true;			
	}
	
	function install()
	{
		if ((!parent::install()) or (!$this->installPatch()) )
			return false;
		return true; 
	}
	
	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitbtn'))
		{				
			$val = (Tools::getValue('img'));
			if (!$val)
				$errors[] = $this->l('error al guardar la ruta de las imágenes');
			else
				Configuration::updateValue('SYNCG2K_IMG', $val);
				
			$val = (Tools::getValue('servidor'));
			if (!$val)
				$errors[] = $this->l('Error al guardar la ruta del servidor');
			else
				Configuration::updateValue('SYNCG2K_SERVIDOR', $val);
								
			if (isset($errors) AND sizeof($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Cambios guardados'));
		}
		else if(Tools::isSubmit('submitBorrarImg')){
			$this->img_tmp=_PS_ROOT_DIR_.Configuration::get('SYNCG2K_IMG');
			$directory=$this->img_tmp;
			if($directory != ''){
				$dir=scandir($directory);
				foreach($dir as $d){
					if(($d == '.') or ($d == '..') or ($d == 'index.html') or ($d == 'index.php')) continue;
					unlink($directory.'/'.$d);	
				}
			}
			
		}
		return $output.$this->displayForm();
	}
	
	public function displayForm()
	{	
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" width="300">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Configuración').'</legend>
				<div class="margin-form">
				<p>'.$this->l('Ruta a servidor').'</p>
					<input type="text" size="40" name="servidor" value="'.Tools::getValue('servidor', Configuration::get('SYNCG2K_SERVIDOR')).'" />
					<small>Ejemplo: www.example.com/</small>
				</div>				
				<div class="margin-form">
				<p>'.$this->l('Directorio temporal de imágenes').'</p>
					'._PS_ROOT_DIR_.'<br><input type="text" size="40" name="img" value="'.Tools::getValue('img', Configuration::get('SYNCG2K_IMG',"/img/syncg2k/")).'" />
					<small>por defecto: /img/synckg2k</small>
				</div>				
				<center><input type="submit" name="submitbtn" value="'.$this->l('Guardar').'" class="button" /></center>
				<p>Puede programar como tarea el siguiente enlace <br> Cron: <a target="_blank" href="http://'.Configuration::get('PS_SHOP_DOMAIN').__PS_BASE_URI__.'modules/'.$this->name.'/cron.php" >http://'.Configuration::get('PS_SHOP_DOMAIN').__PS_BASE_URI__.'modules/'.$this->name.'/cron.php</a> </p>
				<p>Recuerde regenearar las miniaturas cada vez que actualice la página: <b><a href="index.php?tab=AdminImages&token=80ef1fedfa7d967257396bf454f9134c">Regenerar miniaturas</a></b></p>
			</fieldset>
		</form><br>';
		
		$directory=$this->img_tmp;
		$tam=0;
			if($directory != ''){
				$dir=scandir($directory);
				foreach($dir as $d){
					if(($d == '.') or ($d == '..') or ($d == 'index.html') or ($d == 'index.php')) continue;
					$tam+= (float)(filesize($directory.'/'.$d));
				}
			}
			
		$output.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" width="300">
			<fieldset><legend>'.$this->l('Imágenes almacenadas').'</legend>				
				<div class="margin-form"><p>Imágenes de productos: '.(int)(count($dir)-2).'</p>
					<p>Tamaño ocupado: '.number_format((float)(($tam/1024)/1024),2).' MB </p>
				</div>
				<center><input type="submit" name="submitBorrarImg" value="'.$this->l('Borrar imágenes descargadas').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}
	
	static function DameMarca($refG2k){ 
		$row = Db::getInstance()->getRow('SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer WHERE g2k = "'.$refG2k.'"');
		if(isset($row['id_manufacturer']))
			return $row['id_manufacturer'];
		else return 0;  
	}			
	static function CreaMarca($marca,$titulo,$lang=3){ 
		Db::getInstance()->Execute('insert into '._DB_PREFIX_.'manufacturer (name,date_add,date_upd,active,g2k) values ("'.$titulo.'",now(),now(),1,"'.$marca.'" ) ');
		echo Db::getInstance()->getMsgError();
		$id=Db::getInstance()->Insert_ID();
		Db::getInstance()->Execute('insert into '._DB_PREFIX_.'manufacturer_lang (id_manufacturer,id_lang,description,short_description,meta_title,meta_keywords,meta_description) values ('.$id.','.$lang.',"","","'.$titulo.'","'.$titulo.'","'.$titulo.'" ) ');	
		echo Db::getInstance()->getMsgError(); 			
	}	
	static function DameGrupo($refG2k){
		$row = Db::getInstance()->getRow('SELECT id_attribute_group FROM '._DB_PREFIX_.'attribute_group WHERE g2k = "'.$refG2k.'"');
		if( isset($row['id_attribute_group']))
			return $row['id_attribute_group'];
		else return 0;
	}		
	function CreaGrupo($refG2k,$titulo,$lang=3){		
		Db::getInstance()->Execute("insert into "._DB_PREFIX_."attribute_group (is_color_group,g2k) values (0,'".$refG2k."')");
		echo Db::getInstance()->getMsgError();		
		$id_grupo=Db::getInstance()->Insert_ID();
		Db::getInstance()->Execute("insert into "._DB_PREFIX_."attribute_group_lang (id_attribute_group,id_lang,name,public_name) values (".$id_grupo.",".$lang.",'".$titulo."','".$titulo."')" );
		echo Db::getInstance()->getMsgError();
		return $id_grupo;
	}

	static function DameAtributo($grupo,$refG2k){
		$row = Db::getInstance()->getRow('SELECT a.id_attribute FROM '._DB_PREFIX_.'attribute a join '._DB_PREFIX_.'attribute_group g on a.id_attribute_group=g.id_attribute_group  WHERE g.g2k="'.$grupo.'" and  a.g2k = "'.$refG2k.'"');
		if(isset($row['id_attribute']))
			return $row['id_attribute'];
		else return 0;	
	}
		
	function CreaAtributo($grupo,$refG2k,$titulo,$lang=3){
		Db::getInstance()->ExecuteS("insert into "._DB_PREFIX_."attribute (id_attribute_group,color,g2k) values (".$grupo.",'#000000','".$refG2k."')");
		echo Db::getInstance()->getMsgError();
		$id=Db::getInstance()->Insert_ID();
		Db::getInstance()->ExecuteS("insert into "._DB_PREFIX_."attribute_lang (id_attribute,id_lang,name) values (".$id.",".$lang.",'".$titulo."')" );
		echo Db::getInstance()->getMsgError();
	}
	
	static function DameFamilia($refG2k){
		$refG2k=utf8_encode($refG2k);
		$row = Db::getInstance()->getRow('SELECT id_category FROM '._DB_PREFIX_.'category WHERE g2k = "'.$refG2k.'"');
		if(isset($row['id_category']))
			return $row['id_category'];
		else return 0;
	}
	
	function CreaFamilia($familia,$titulo,$lang=3,$padre=1){
		if($padre == 1) $depth=1;
		else{
			$depth=2;
			$row=Db::getInstance()->getRow("select id_category from ps_category where g2k='".$padre."'");
			$padre=(int)$row['id_category'];
			if(!$padre){$padre=1;$depth=1;}
		}
	
		$row=Db::getInstance()->getRow("select max(nleft)+1 as nleft,max(nright)+1 as nright,max(position)+1 as position from ps_category");			
		echo 'max'.Db::getInstance()->getMsgError(); 		
		Db::getInstance()->ExecuteS('insert into '._DB_PREFIX_.'category (id_parent,level_depth,nleft,nright,active,date_add,date_upd,position,g2k) values 
			('.$padre.','.$depth.','.$row['nleft'].','.$row['nright'].',1,now(),now(),'.$row['position'].', "'.$familia.'" ) ');
		echo 'id:'.Db::getInstance()->getMsgError(); 	
		$id=Db::getInstance()->Insert_ID();
		Db::getInstance()->ExecuteS('insert into '._DB_PREFIX_.'category_group (id_category,id_group) values ('.$id.',1)');
		echo 'group:'.Db::getInstance()->getMsgError(); 	
		Db::getInstance()->ExecuteS('insert into '._DB_PREFIX_.'category_lang (id_category,id_lang,name,description,link_rewrite,meta_title,meta_keywords,meta_description) 
			values ('.$id.','.$lang.',"'.$titulo.'","'.$titulo.'","'.str2url($titulo).'","'.$titulo.'","'.$titulo.'","'.$titulo.'")');
		echo 'lang'.Db::getInstance()->getMsgError(); 				
	}
	function ActualizaFamilia($id,$titulo,$lang=3){
		$url=str2url($titulo);		
		Db::getInstance()->ExecuteS("update "._DB_PREFIX_."category_lang set name='$titulo', description='$titulo', link_rewrite ='$url', meta_title='$titulo', meta_keywords='$titulo', meta_description='$titulo' where id_category=$id and id_lang=$lang"); 
		echo Db::getInstance()->getMsgError();
	}

	static function DameProducto($refG2k){
		$row=Db::getInstance()->getRow('
		SELECT `id_product`
		FROM `'._DB_PREFIX_.'product` p
		WHERE p.`reference` = "'.$refG2k.'"');
		if(isset($row['id_product']))
			return $row['id_product'];
		else {
			$id=SyncG2k::DameCombinacion($refG2k);
			if($id) return -1;
			else return 0;
		}
	}
			
	static function CreaProducto($ref,$titulo,$precio,$coste,$fab,$cat,$iva,$desc_corta,$desc_larga,$peso,$ancho,$alto,$profundo,$lang=3,$pro=0){
		$url=str2url($titulo);
		$meta=str2meta($titulo);
		$titulo=str2html($titulo);
		$desc_corta=str2html($desc_corta);
		$desc_larga=str2html($desc_larga);
		$position=Db::getInstance()->getRow("select max(position) as position from ps_category_product");
		$position=$position['position'];	
		if($position == null) $position = 0;
		if(!$peso) $peso=0;
		if(!$ancho) $ancho=0;
		if(!$alto) $alto=0;
		if(!$profundo) $profundo=0;

		if($precio < 0.1 ) $disponibilidad=0;
		else $disponibilidad=1;

		Db::getInstance()->ExecuteS("INSERT INTO `ps_product` (
			`id_supplier`, `id_manufacturer`, `id_tax_rules_group`, `id_category_default`, `on_sale`, `online_only`, `ecotax`, `quantity`, `minimal_quantity`,
			`price`, `wholesale_price`, `unit_price_ratio`, `additional_shipping_cost`, `unity`, `reference`, `supplier_reference`, `location`, `width`, `height`,
			`depth`, `weight`, `out_of_stock`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`, `active`, `available_for_order`, `condition`, 
			`show_price`, `indexed`, `cache_is_pack`, `cache_has_attachments`, `cache_default_attribute`, `date_add`, `date_upd`) 
		VALUES(	$pro,$fab,	$iva,$cat, 0,0, '0.000000', ".$disponibilidad.",1,
			$precio, $coste,'0.000000', '0.00','','".$ref."','','',$ancho, $alto	, 
			$profundo, $peso,2, 0,0,0,0,1, 1,'new',
			1,1, 0,0,0,now(),now())");
			
		echo Db::getInstance()->getMsgError('Error al crear producto');
		$id=Db::getInstance()->Insert_ID();
		if($id!=0){
			
			Db::getInstance()->ExecuteS('insert into '._DB_PREFIX_.'product_lang (id_product,id_lang,description,description_short,link_rewrite,meta_description,meta_keywords,meta_title,name)
				value('.$id.','.$lang.' ,"'.$desc_larga.'","'.$desc_corta.'","'.$url.'","'.$meta.'","'.$meta.'","'.$titulo.'","'.$titulo.'")');
			echo Db::getInstance()->getMsgError('Eror al crear producto_lang');	
			Db::getInstance()->ExecuteS('insert into '._DB_PREFIX_.'category_product (id_category,id_product,position) values ('.$cat.','.$id.','.$position.')');
			echo Db::getInstance()->getMsgError('error al crear producto_categoria');
			
			Db::getInstance()->ExecuteS("INSERT INTO "._DB_PREFIX_."product_shop (`id_product` ,`id_shop` ,`id_category_default` ,`id_tax_rules_group` ,`on_sale` ,`online_only` ,`ecotax` ,`minimal_quantity` ,`price` ,`wholesale_price` ,
				`unity` ,`unit_price_ratio` ,`additional_shipping_cost` ,`customizable` ,`uploadable_files` ,`text_fields` ,`active` ,`available_for_order` ,`available_date` ,`condition` ,
				`show_price` ,`indexed` ,`visibility` ,`cache_default_attribute` ,`advanced_stock_management` ,`date_add` ,`date_upd`)
			VALUES ($id, '1', $cat, '1', '0', '0', '0.000000', '1', '$precio', '$coste',
			 	'', '0.000000', '0.00', '0', '0', '0', '1', '1', '0000-00-00', 'new', 
			 	'1', '1', 'both', '0', '0', now(), now())");
			echo Db::getInstance()->getMsgError('error al crear producto_tienda');
		}		
		return $id;
	}

	static function ActualizaProducto($producto,$titulo,$desc_corta,$desc_larga,$precio=0,$coste=0,$iva,$fab,$cat,$peso=0,$ancho=0,$alto=0,$profundo=0,$lang=3,$pro=0){				
		$url=str2url($titulo);				
		$meta=str2meta($titulo);
		$titulo=str2title($titulo);
		$desc_corta=str2html($desc_corta);
		$desc_larga=str2html($desc_larga);		
		Db::getInstance()->Execute('update '._DB_PREFIX_.'product set 
			id_manufacturer='.(int)($fab).', 
			id_supplier = '.(int)$pro.',
			available_for_order=1,
			active=1,
			id_tax_rules_group='.(int)($iva).', 
			price= '.(float)($precio).' ,
			wholesale_price= '.(float)($coste).',
			width='.(float)($ancho).', 
			height='.(float)($alto).', 
			depth='.(float)($profundo).', 
			weight='.(float)($peso).' 
			 where id_product= '.(int)($producto));						
		echo Db::getInstance()->getMsgError("error al actualizar el producto");
		
		$sql='update '._DB_PREFIX_.'product_lang set 
			name= "'.$titulo.'"';
			$sql.=' ,link_rewrite= "'.$url.'"';
			if($desc_corta != '') $sql.=' ,description_short= "'.$desc_corta.'"'; 
			if($desc_larga != '') $sql.=' ,description= "'.$desc_larga.'"';		
			if($meta != '') $sql.=' ,meta_description= "'.$meta.'", meta_keywords = "'.$meta.'"';
			$sql.=' where id_product= '.(int)($producto);		
		Db::getInstance()->Execute($sql);
		echo Db::getInstance()->getMsgError("error al actualizar el producto_lang"); 
		
		Db::getInstance()->Execute('update '._DB_PREFIX_.'product_shop set
			available_for_order=1,
			active=1,
			id_tax_rules_group='.(int)($iva).', 
			price= '.(float)($precio).' ,
			wholesale_price= '.(float)($coste).'
			where id_product= '.(int)($producto));			 
		echo Db::getInstance()->getMsgError("error al actualizar el producto_tienda");
		
		//Db::getInstance()->Execute('update '._DB_PREFIX_.'category_product set id_category='.(int)($cat).' where id_category <> 1 and id_product= '.(int)($producto));
		//echo Db::getInstance()->getMsgError("error al actualizar el producto_cat"); 		 		 		 		
	}
	
	static function DameImagen($referencia,$lang=3){
		return Db::getInstance()->getRow('
		SELECT i.`cover`, i.`id_image`, il.`legend`, i.`position`
		FROM `'._DB_PREFIX_.'image` i
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($lang).')
		JOIN `'._DB_PREFIX_.'products` p on p.id_product=i.id_product
		WHERE p.`reference` = "'.$referencia.'"
		ORDER BY `position`');
	}
		
	function CreaImagen($producto,$imagen,$titulo,$pos,$cov,$lang=3){
			
		if($producto && ($imagen != '')){			
			$row=Db::getInstance()->getRow('
			SELECT i.`id_image`
			FROM `'._DB_PREFIX_.'image` i 		
			WHERE i.`id_product` = '.(int)($producto). ' and i.position = '.(int)($pos).'');

				$file=$this->img_tmp.'/'.$producto.'-'.$pos.'.jpg';
				if (!file_exists($file)){ //si no ha sido descargada				
					if (fopen($imagen, "r")){	//si se puede descargar					
						file_put_contents($file, file_get_contents($imagen));
						
						//comprobamos que la imágen sea válida
						$image= getimagesize($file);
						$tipo=$image['mime'];
						if(($tipo != "image/gif") && ($tipo != "image/jpeg") && ($tipo != "image/png")){
							echo "\n"."<li>La imágen $pos no es una imágen válida para el producto: $producto - $tipo <a target='_blank' href='$imagen'>".$imagen."</a></li>";
							unlink($file);
							return false;
						}					
									
					}else
					{
						echo "\n"."<li>La imágen $pos no existe para el producto: $producto - <a target='_blank' href='$imagen'>".$imagen."</a></li>";
						return false;
					}
				}
			if(!isset($row['id_image'])){			
				Db::getInstance()->ExecuteS("insert into "._DB_PREFIX_."image (id_product,position,cover) values (".$producto.",".$pos.",".$cov.")");
				echo Db::getInstance()->getMsgError('error al insertar imágen');
				$id=Db::getInstance()->Insert_ID();
				Db::getInstance()->ExecuteS("insert into "._DB_PREFIX_."image_lang (id_image,id_lang,legend) values (".$id.",".$lang.",'".$titulo."')");
				echo Db::getInstance()->getMsgError('error al insertar imágen_lang');
				
				Db::getInstance()->ExecuteS("insert into "._DB_PREFIX_."image_shop (id_image,id_shop,cover) values ($id,1,1)"); 
				echo Db::getInstance()->getMsgError('error al insertar imágen_tienda');
				
				symlink($file,$this->img_tmp.'/../p/'.$producto.'-'.$id.'.jpg');
			}
		}
	}
	static function DameCombinacion($refG2k){
		$row = Db::getInstance()->getRow('SELECT id_product_attribute FROM '._DB_PREFIX_.'product_attribute where reference = "'.$refG2k.'"');
		if( isset($row['id_product_attribute']))
			return $row['id_product_attribute'];
		else return 0;	
	}

	function CreaCombinacion($producto_base,$atributo,$ref,$precio,$coste=0,$peso=0,$default=0){
		$refG2k=utf8_encode($refG2k);
		
		Db::getInstance()->ExecuteS("insert into "._DB_PREFIX_."product_attribute (id_product,reference,supplier_reference,wholesale_price,price,				ecotax,quantity,weight,				unit_price_impact,default_on,minimal_quantity)
		  values (".$producto_base.",'".$ref."','".$ref."',".(float)($coste).",".(float)($precio).",0,1,".(float)($peso).",1,".(int)($default).",1 ) "); 
		$id=Db::getInstance()->Insert_ID();
		echo Db::getInstance()->getMsgError(); 
		Db::getInstance()->ExecuteS("insert into "._DB_PREFIX_."product_attribute_combination (id_attribute,id_product_attribute) value (".$atributo.",".$id.")");
		echo Db::getInstance()->getMsgError(); 
		
		return $id;
	}
	
 	static function ActualizaCombinacion($combinacion,$precio=0,$coste=0,$default=0){
 		Db::getInstance()->ExecuteS("update "._DB_PREFIX_."product_attribute  set wholesale_price=".(float)($coste).",price=".(float)($precio).",default_on=".(int)($default)." where id_product_attribute= ".(int)($combinacion));
 		echo Db::getInstance()->getMsgError(); 		
 	}

	static function DameIva($iva){		
		$row = Db::getInstance()->getRow('SELECT id_tax_rules_group FROM '._DB_PREFIX_.'tax t join '._DB_PREFIX_.'tax_rule r on t.id_tax=r.id_tax WHERE t.rate = '.(float)($iva));
		echo Db::getInstance()->getMsgError();
		if(isset($row['id_tax_rules_group']))
			return $row['id_tax_rules_group'];
		else return 0;
	}
	 	
	function cron(){
		header( 'Content-type: text/html; charset=utf-8' );
		if (!$this->active) die('El módulo no está activo.');
		if (!file_exists($this->img_tmp)) die('El directorio temporal de imágenes no existe.');

		$inicio=time();
		echo '<html">
				<head >
					<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
					<title >Syncg2k - Sincronizador de productos con G2k</title>
					<style type="text/css">
						.crear{color:green;}
						.existe{color:blue;}
						.modificar{color:orange;}
						.error{color:red;}
						.titulo{font-size:16px;}
					</style>
				</head>
			<body>';
		
		$servidor=Configuration::get("SYNCG2K_SERVIDOR");		
		$lang= Configuration::get("PS_LANG_DEFAULT");
		
		
		//MARCAS	
		if(!fopen($servidor.'marcas.php','r')) die('No se han podido obterner las marcas:'.$servidor.'marcas.php');
		$marcas=parse_csv(file_get_contents($servidor.'marcas.php'));		
		echo '<ul> <lh >Marcas: '.count($marcas).'</lh> ';
		foreach($marcas as $m){					
			if(! $this->DameMarca($m['MARCA'])){
				echo "\n".'<li class="crear">'.$m['MARCA'].' - '.$m['TITULO'].' - Crear </li>'; 
				$this->CreaMarca($m['MARCA'],$m['TITULO'],$lang);		
				if (sizeof($errors)) echo $errors;		
			}
			//else echo '<li class="existe">'.$m['MARCA'].' - '.$m['TITULO'].' - Existe </li>';
		}	
		echo '</ul>';
		unset($marcas);
	
		//GRUPOS Y AGRUPACIONES
		if(!fopen($servidor.'atributos.php','r')) echo ('No se han podido obterner los atributos');
		$grupos=parse_csv(file_get_contents($servidor.'atributos.php'));		
		echo '<ul> <lh >Grupos y atributos: '.count($grupos).'</lh> ';
		foreach($grupos as $g){
			$id_grupo=$this->DameGrupo($g['GRUPO']);
			if(! $id_grupo ){
				echo "\n".'<li class="crear">GRUPO:'.$g['GRUPO'].' - '.$g['GRUPO_TITULO'].' - Crear </li>';
				$id_grupo=$this->CreaGrupo($g['GRUPO'],$g['GRUPO_TITULO'],$lang);
			}
			
			if(!$this->DameAtributo($g['GRUPO'],$g['ATRIBUTO'])){
					echo "\n".'<li class="crear">Grupo:'.$id_grupo.'.|ATRIBUTO:'.$g['ATRIBUTO'].' - '.$g['ATRIBUTO_TITULO'].' - Crear </li>';
					$this->CreaAtributo($id_grupo,$g['ATRIBUTO'],$g['ATRIBUTO_TITULO']);
			}
			else echo '<li class="existe">'.$g['ATRIBUTO'].' - '.$g['ATRIBUTO_TITULO'].' - Existe </li>';
						
		}	
		echo '</ul>';
		unset($grupos);		

		//FAMILIAS 
		if(!fopen($servidor.'familias.php','r')) die('No se han podido obterner las familias');
		$familias=parse_csv(file_get_contents($servidor.'familias.php'));		
		echo '<ul> <lh >Familias: '.count($familias).'</lh> ';
		foreach($familias as $f){			
			$id=$this->DameFamilia($f['FAMILIA']);
			if(!$id){
				echo "\n".'<li class="crear">'.$f['FAMILIA'].' - '.$f['TITULO'].' - '.$f['PADRE'].' - Crear </li>'; 
				$this->CreaFamilia($f['FAMILIA'],$f['TITULO'],$lang,$f['PADRE']);				
			}else{
				$this->ActualizaFamilia($id,$f['TITULO'],3);
				//echo "\n".'<li class="existe">'.$f['FAMILIA'].' - '.$f['TITULO'].' - '.$f['PADRE'].' - Existe </li>';
			}
		}	
		echo '</ul>';		
		unset($familias);

		//PRODUCTOS	
		if(!fopen($servidor.'productos.php','r')) die('No se han podido obterner los productos');
		$productos=parse_csv(file_get_contents($servidor.'productos.php'));		
		Db::getInstance()->Execute(' update '._DB_PREFIX_.'product set available_for_order=0');
		Db::getInstance()->Execute(' update '._DB_PREFIX_.'product_shop set available_for_order=0');
		echo '<ol> <lh >Productos: '.count($productos).'</lh> ';
		foreach($productos as $p){
			//inicialización de variables

			//obtenemos ids de la página
			$id=$this->DameProducto($p['ARTICULO']);					
			$fab=$this->DameMarca($p['MARCA']);			
			$cat=$this->DameFamilia($p['FAMILIA']);			
			$iva=$this->DameIva($p['IVA']); 

			//comprobamos valores mínimos
			if(!$iva) $iva=1; //iva normal en prestashop
			if(!$fab or !$cat or !$iva) {echo "\n"."<li>No se encuentra alguno de los campos necesarios: $fab|$cat|$iva|".$p['ARTICULO'].'</li>'; continue;}
		
			//ponemos título al artículo
			if ($p['TITULO_WEB'] == '') $titulo=$p['TITULO'];
			else $titulo=$p['TITULO_WEB'];
			if ($titulo == '') $titulo=$p['ARTICULO'];			
			
			//************ MODIFICACIÓN ESPECIFICA PARA CANO pvp/1.21		 
			$precio=(float)$p['PRECIO_VENTA']/1.21;
			$coste=(float)str_replace(",",".",$p['P_COSTE']);			
			//************
			if($id != -1){ //comprobamos si es una combinación, se actualizarán más tarde
				if($id==0){					 
					$id=$this->CreaProducto($p['ARTICULO'],$titulo,$precio,$coste,$fab,$cat,$iva,$p['DESCRIPCION_CORTA'],$p['DESCRIPCION_LARGA'],$p['PESO'],$p['ANCHO'],$p['ALTO'],$p['PROFUNDO'],$lang,$p['PROVEEDOR']);
					echo "\n".'<li class="crear">Marca:'.$fab.'Familia:'.$cat.'Iva:'.$iva. ' - '.$p['ARTICULO'].' - '.$titulo.'-'.$precio.'-'.$p['P_COSTE'].' - Crear </li>';
				} else {
					$this->ActualizaProducto($id,$titulo,$p['DESCRIPCION_CORTA'],$p['DESCRIPCION_LARGA'],$precio,$p['P_COSTE'],$iva,$fab,$cat,$p['PESO'],$p['ANCHO'],$p['ALTO'],$p['PROFUNDO'],$lang,$p['PROVEEDOR']);
					//echo "\n".'<li class="existe">Marca:'.$fab.'Familia:'.$cat.'Iva:'.$iva.' - '.$p['ARTICULO'].' - '.$titulo.'-'.$precio.'-'.$p['P_COSTE'].' - Existe </li>';
				}
				if(($p['IMAGEN1'] != '') and !strstr($p['IMAGEN1'],"Array")) $this->CreaImagen($id,$p['IMAGEN1'],$titulo,1,1,$lang);
				if(($p['IMAGEN2'] != '') and !strstr($p['IMAGEN2'],"Array")) $this->CreaImagen($id,$p['IMAGEN2'],$titulo,2,0,$lang);
				if(($p['IMAGEN3'] != '') and !strstr($p['IMAGEN3'],"Array")) $this->CreaImagen($id,$p['IMAGEN3'],$titulo,3,0,$lang);
			}
			flush();
			ob_flush();
		}
		echo '</ol>';
		unset($productos);
		
	
		//COMBINACIONES 
		if(!fopen($servidor.'productos_combinados.php','r')) echo ('No se han podido obterner los productos combianados');
		if(!fopen($servidor.'articulos_atributos.php','r')) echo ('No se han podido obterner los atributos');
		$comb=parse_csv(file_get_contents($servidor.'productos_combinados.php'));
		$atri=parse_csv(file_get_contents($servidor.'articulos_atributos.php'));				
		echo '<ul> <lh >Combinaciones</lh> ';
		foreach($comb as $c){
			$producto_base=$this->DameProducto($c['MODELO']);								
			$fab=$this->DameMarca($c['MARCA']);			
			$cat=$this->DameFamilia($c['FAMILIA']);			
			$iva=$this->DameIva($c['IVA']);
			
			//buscamos el atributo
			foreach($atri as $a){
				if($a['ARTICULO']==$c['ARTICULO']){
					$grupo=$a['GRUPO'];
					$atributo=$a['ATRIBUTO'];
				}
			}
			$atributo=$this->DameAtributo($grupo,$atributo);
			
			if(!$fab or !$cat or !$iva or !$atributo) {echo 'No se encuentra alguno de los campos necesarios'; continue;}
			
			if ($c['TITULO_WEB'] == '') $titulo=$c['TITULO'];
			else $titulo=$c['TITULO_WEB'];
			
			if(! $producto_base){
				echo "\n".'<li class="crear">Modelo:'.$c['MODELO'].'- Crear </li>';					
				$producto_base=$this->CreaProducto($c['MODELO'],$titulo,0,0,$fab,$cat,$iva,$c['DESCRIPCION_CORTA'],$c['DESCRIPCION_LARGA'],$c['PESO'],$c['ANCHO'],$c['ALTO'],$c['PROFUNDO'],$lang);				  
			}
			else $this->ActualizaProducto($producto_base,$titulo,$c['DESCRIPCION_CORTA'],$c['DESCRIPCION_LARGA'],0,0,$iva,$fab,$cat,$c['PESO'],$c['ANCHO'],$c['ALTO'],$c['PROFUNDO'],$lang);
					
					
			if($producto_base != $prod_ant) {
				$default=1;
				$prod_ant=$producto_base;
			}
			else $default=0;
			
			$combinacion=$this->DameCombinacion($c['ARTICULO']);
			if(!$combinacion){
				echo "\n".'<li class="crear">'.$c['ARTICULO'].' - '.$titulo.'-'.$producto_base.'-'.$fab.'-'.$cat.'-'.$iva.'-'.$atributo.' - Crear </li>';												 
				$combinacion=$this->CreaCombinacion($producto_base,$atributo,$c['ARTICULO'],$c['PRECIO_VENTA'],$c['P_COSTE'],$c['PESO'],$default);		
			}else{
				 echo "\n".'<li class="existe">'.$c['ARTICULO'].' - '.$titulo.'-'.$producto_base.'-'.$fab.'-'.$cat.'-'.$iva.'-'.$atributo.' - Existe </li>';
				 $this->ActualizaCombinacion($combinacion,$c['PRECIO_VENTA'],$c['P_COSTE'],$defaul);
			}			
			$this->CreaImagen($producto_base,$c['IMAGEN1'],$titulo,1,1,$lang);
			$this->CreaImagen($producto_base,$c['IMAGEN1'],$titulo,2,0,$lang);
			$this->CreaImagen($producto_base,$c['IMAGEN1'],$titulo,3,0,$lang);
		}	
		echo '</ul>';
		unset($comb);
		
		//ACTUALIZAMOS INDECES DE BUSQUEDA
		echo "\n"."<p>Actualizando índice de busqueda</p>";
		//$tmp=file_get_contents('http://'.Configuration::get('PS_SHOP_DOMAIN').__PS_BASE_URI__.'administrator/searchcron.php?token=5Q7a0Dgd');
			$tmp=file_get_contents('http://www.umigranada.com/tienda/adminonac/searchcron.php?full=1&token=T6pCyiBO');
		echo '<p>Refenere las miniaturas desde el siguiente <a href="http://umigranada.com/tienda/adminonac/index.php?tab=AdminImages">enlace</a></p>';
		echo "\n".'<br>Generando sitemap';
			$tmp=file_get_contents("http://www.umigranada.com/tienda/modules/gsitemap/gsitemap-cron.php?&token=6b2cfab5a7&GSITEMAP_ALL_CMS=1&GSITEMAP_ALL_PRODUCTS=1");
	  	echo 'Tiempo usado: '.number_format((dametiempo()- $inicio),2,",","")." SEGUNDOS";		
		echo '</body></html>';
				
		}
}
?> 
