<?php
class myTools
{

	/**
	* Get a value from $_POST / $_GET
	* if unavailable, take a default value
	*
	* @param string $key Value key
	* @param mixed $defaultValue (optional)
	* @return mixed Value
	*/
	static public function getValue($key, $defaultValue = false)
	{
	 	if (!isset($key) OR empty($key) OR !is_string($key))
			return false;
		$ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

		if (is_string($ret) === true)
			$ret = urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret)));
		return !is_string($ret)? $ret : stripslashes($ret);
	}

	static public function getHttpHost($http = false, $entities = false)
	{
		$host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
		if ($entities)
			$host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
		if ($http)
			$host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$host;
		return $host;
	}

	/*
	 * Clean a string
	 *
	 * @param String $separator The string separator
	 * @param String $string The string to convert
	 * @param boolean $useHtml2text TRUE to use the Html2text library
	 */
	static public function f_convert_text($separator, $string, $useHtml2text)
	{
	    $string = trim($string);
		$string = preg_replace('#<br ?/?>#isU', ' ', $string);
		$string = preg_replace('/(\r\n|\n|\r)/', ' ', $string);
		$string = strip_tags(html_entity_decode(($string),ENT_QUOTES,'UTF-8'));
		$string = ereg_replace("&euro;", "€", $string);
		$string = preg_replace('#\t+#', ' ', $string);
		$string = preg_replace('#\t+#', ' ', $string);
		$string = preg_replace('#'.CHR(10).'+#',' ',$string);
		$string = str_replace(CHR(9)," ",$string);
		if($separator!="")
			$string = str_replace($separator," ",$string);
		$string = preg_replace('# +#', ' ', $string);
		if($useHtml2text){
			$string=&new html2text($string);
			$string=$string->get_text();
		}
		return trim($string);
	}

	// /////////////////////////////////////////
	// GENERATION ARBRE
	// Crée un arbre des catégorie avec checkbox
	// /////////////////////////////////////////
	static public function base_arbre($ps_,$module_name){

	// Lit la catégorie parent début de l'arbre
	$sql = 'SELECT * from '.$ps_.'category LEFT JOIN '.$ps_.'category_lang ON '.$ps_.'category.id_category = '.$ps_.'category_lang.id_category WHERE level_depth=1 and id_parent = 1 and id_lang=2 and active=1';
	$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());

	while($data = mysql_fetch_assoc($req))
    {

		$value = $data['id_category'];

		$sql1 = 'SELECT parameter_value from '.$ps_.'guide_parameter WHERE parameter_guide="'.$module_name.'" and parameter_value='.$value.' and parameter_name=\'id_catego\'';
		$req1 = mysql_query($sql1) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		while($data1 = mysql_fetch_assoc($req1))
    	{
		   $parameter_value_m = "";
		   $parameter_value_m = $data1['parameter_value'];
   		}

		if(!isset($parameter_value_m)){$parameter_value_m="";}
		if($parameter_value_m==$data['id_category'])
			{$parameter_value_m= "checked";}
		else
			{$parameter_value_m= "";}



		$nbre_product = count_article_categorie($ps_,$value);
		echo "<li><span class=\"folder\">";
		echo "<input type='checkbox' $parameter_value_m value=$value name=id_cat[]> ".$data['id_category']." ".$data['name']." $nbre_product<br> \n";
		echo "</span>";
		$id_category = $data['id_category'];
		$level = $data['level_depth'];
		// On crée la branche
		self::branche($id_category,$level,$ps_,$module_name);
	}

	}

	// Crée une branche pour l'arbre
	static public function branche($id_category,$level,$ps_,$module_name){

	$level = $level;
	$level_next_category = $level+1;

	$sql = 'SELECT * from '.$ps_.'category LEFT JOIN '.$ps_.'category_lang ON '.$ps_.'category.id_category = '.$ps_.'category_lang.id_category WHERE '.$ps_.'category.id_parent = '.$id_category.' and id_lang=2 and active=1';
	$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());

	while($data = mysql_fetch_assoc($req))
    {

		$value = $data['id_category'];


		$sql1 = 'SELECT parameter_value from '.$ps_.'guide_parameter WHERE parameter_guide="'.$module_name.'" and parameter_value='.$value.' and parameter_name=\'id_catego\'';
		$req1 = mysql_query($sql1) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		while($data1 = mysql_fetch_assoc($req1))
    	{
   		$parameter_value = "";
  		$parameter_value = $data1['parameter_value'];
   		}

		if(!isset($parameter_value)){$parameter_value="";}
		if($parameter_value==$data['id_category'])
			{$parameter_value= "checked";}
		else
			{$parameter_value= "";}

		$nbre_product = count_article_categorie($ps_,$value);
		echo "<ul><li><span class=\"folder\">";
		echo "<input type='checkbox' $parameter_value value=$value name=id_cat[]>".$data['id_category']."  ".$data['name']." $nbre_product<br> \n";
		echo "</span></li></ul>";
		$next_id_category = $data['id_category'];
		// Récursif
		self::branche($next_id_category,$level_next_category,$ps_,$module_name);
	}

	}
}