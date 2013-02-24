<?php
	function f_convert_text($string){
		htmlspecialchars($string);
		$string=strip_tags(html_entity_decode(($string),ENT_QUOTES,'UTF-8'));
		strip_tags($string); 

		//27/05/2009 - Fabien LAHAULLE - suppression des balises <br/>
		$string = preg_replace('#<br ?/?>#isU', ' ', $string);

		$string = ereg_replace("<[^>]*>", "", $string);
		// retire les retours à la ligne
		$string = preg_replace("/(\r\n|\n|\r)/", " ", $string); 
		
		
		$string = ereg_replace("&agrave;", "à", $string);
		$string = ereg_replace("&acirc;", "â", $string);
		$string = ereg_replace("&auml;", "ä", $string);
		$string = ereg_replace("&ccedil;", "ç", $string);
		$string = ereg_replace("&egrave;", "è", $string);
		$string = ereg_replace("&eacute;", "é", $string);
		$string = ereg_replace("&Eacute;", "é", $string);
		$string = ereg_replace("&ecirc;", "ê", $string);
		$string = ereg_replace("&euml;", "ë", $string);
		$string = ereg_replace("&icirc;", "î", $string);
		$string = ereg_replace("&ocirc;", "ô", $string);
		$string = ereg_replace("&ugrave;", "ù", $string);
		$string = ereg_replace("&ucirc;", "û", $string);
		$string = ereg_replace("&rsquo;", "'", $string);
		$string = ereg_replace("&deg;", "°", $string);
		// Conversion des monnaies
		$string = ereg_replace("&euro;", "EUR", $string);
		// Espacement
		$string = ereg_replace("&nbsp;", " ", $string);	
		// on vires les ;
		$string = ereg_replace(";", ":", $string);	
						
		return $string;	
	}
	
	//Avi - nouvelle fonction nettoyage de caractères html
	function f_convert_text2($strSeparateur, $string, $useHtml2text){
		$string = trim($string);
		$string = preg_replace('#<br ?/?>#isU', ' ', $string);
		$string = preg_replace('/(\r\n|\n|\r)/', ' ', $string); 
		$string = strip_tags(html_entity_decode(($string),ENT_QUOTES,'UTF-8'));
		$string = ereg_replace("&euro;", "€", $string);
		$string = preg_replace('#\t+#', ' ', $string);
		$string = preg_replace('#\t+#', ' ', $string);
		$string = preg_replace('#'.CHR(10).'+#',' ',$string);
		$string = str_replace(CHR(9)," ",$string);
		if($strSeparateur!="")
			$string = str_replace($strSeparateur," ",$string);
		$string = preg_replace('# +#', ' ', $string);
		if($useHtml2text){
			$string=&new html2text($string);
			$string=$string->get_text();
		}
		return trim($string);
	}
	
	function getDeliveryPriceByRanges($rangeTable)
	{
	$req='
		SELECT d.`id_'.$rangeTable.'`, d.`id_carrier`, d.`id_zone`, d.`price`
		FROM `'._DB_PREFIX_.'delivery` d
		LEFT JOIN `'._DB_PREFIX_.$rangeTable.'` r ON r.`id_'.$rangeTable.'` = d.`id_'.$rangeTable.'`
		WHERE (d.`id_'.$rangeTable.'` IS NOT NULL AND d.`id_'.$rangeTable.'` != 0)
		ORDER BY r.`delimiter1` ASC';
		$rangeTable = pSQL($rangeTable);
		echo $req;
		return Db::getInstance()->ExecuteS($req);
	}
	
	// Forcer telecharger fichier txt
	function forcerTelechargement($nom, $situation, $poids)
	{
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Length: '. $poids);
		header('Content-disposition: attachment; filename='. $nom);
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Expires: 0');
		readfile($situation);
		exit();
	}
	
	// Compter le nombre d'articles dans une catégorie
	function count_article_categorie($ps_,$id_category)
	{
		$sql = 'SELECT COUNT(id_product) as nbre_product FROM '.$ps_.'category_product where id_category='.$id_category.'';
		$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		while($data = mysql_fetch_assoc($req)){$nbre_product = $data['nbre_product'];}
		return "<font color=blue>(".$nbre_product.")</font>";
	}


	// /////////////////////////////////////////
	// GENERATION ARBRE
	// Crée un arbre des catégorie avec checkbox
	// /////////////////////////////////////////
	function base_arbre($ps_,$module_name){
	
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
		branche($id_category,$level,$ps_,$module_name);
	}	

	}

	// Crée une branche pour l'arbre
	function branche($id_category,$level,$ps_,$module_name){
	
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
		branche($next_id_category,$level_next_category,$ps_,$module_name);
	}

	}
	
	//Avi - Refonte du calcul des prix
		// Retourne le prix TTC
	function f_prix_ttc($price,$taux_tva,$price_supp_decl,$reduction_price,$reduction_percent){
		$price_ttc	=(($price*$taux_tva)/100)+$price+$price_supp_decl;
		
		// Si réduction en %
		if($reduction_percent<>0 && $reduction_price==0){
			$price_ttc=$price_ttc*(1.0-($reduction_percent/100.0));		
		}
		// Si montant de réduction
		if($reduction_price<>0 && $reduction_percent==0){
			$price_ttc	=  $price_ttc-$reduction_price;
		}
		$price_ttc	= number_format(round($price_ttc,2),2,".","");
		return $price_ttc;	
	}
														
	//Avi - Refonte du calcul des prix								
	// Retourne le prix sans la réduction
	function f_prix_barre($reduction_percent,$reduction_price,$price,$taux_tva,$price_supp_decl){
	
	if($reduction_percent<>0 OR $reduction_price<>0){
		return f_prix_ttc($price,$taux_tva,$price_supp_decl,0,0);

	}
	return "";	
	}
	
	//Avi - Calul du prix des fdp
	function f_calc_fdp($usefreeshipping,$shipping_method,$delivery_price,$seuil_prix_fdp_offert,$seuil_poids_fdp_offert,$prix,$poids){
		//echo "prix=$prix<br/>";
		//echo "poids=$poids<br/><br/>";
		if($shipping_method==0  && $prix>=$seuil_prix_fdp_offert && $usefreeshipping && $seuil_prix_fdp_offert!=0){
			return 0;
			}
		if($shipping_method==1  && $poids>=$seuil_poids_fdp_offert && $usefreeshipping && $seuil_poids_fdp_offert!=0){
			return 0;
			}
		return $delivery_price;
	}
	
	
	// Retourne le lien pour télécharger le fichier
	//Avi - suppression forcer le telechargement du fichier (bug header)
	function url_file_download($nom_guide,$url_site_base_prestashop,$libelle_guide, $nom_fichier, $extension_fichier){
		echo "<table align=center border=0><tr><td>";
		echo "<p>Lien vers le fichier à spécifier sur \" $libelle_guide\" (cliquer droit : 	&quot;enregistrer sous&quot;):</p>"; 
		echo " <a href=\"http://".$url_site_base_prestashop."modules/$nom_guide/exports/" . $nom_fichier . "." . $extension_fichier ."\" target=\"_blank\">";
		echo "<span style=\"color:#268CCD\">http://".$url_site_base_prestashop."modules/$nom_guide/exports/" . $nom_fichier . "." . $extension_fichier ."</span>";
		echo "</a><p>Le csv peut faire appara&icirc;tre des probl&egrave;mes d&rsquo;accents s&rsquo;il est vu sur le navigateur (ouvrir avec un &eacute;diteur de texte)</p>"; 
		//echo "<a href=\"http://".$url_site_base_prestashop."modules/$nom_guide/download.php?file=" . $nom_fichier . "." . $extension_fichier . "&site=$url_site_base_prestashop\" target=\"_blank\">Télécharger le fichier <img src=\"../modules/$nom_guide/download.png\"></a>";
		echo "</td></tr></table>";
			
	}
	
	// Retourne l'url dans la barre de naviguation
	function f_url_actuelle()
	{
     	return "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	
	// Ecrit dans le fichier	
	function f_write_file($filename,$somecontent){
		
			
			// Assurons nous que le fichier est accessible en écriture
			if (is_writable($filename)) {
			
			    // Dans notre exemple, nous ouvrons le fichier $filename en mode d'ajout
			    // Le pointeur de fichier est placé à la fin du fichier
			    // c'est là que $somecontent sera placé
			    if (!$handle = fopen($filename, 'a')) {
			         echo "Impossible d'ouvrir le fichier ($filename)";
			         exit;
			    }
			
			    // Ecrivons quelque chose dans notre fichier.
			    if (fwrite($handle, $somecontent) === FALSE) {
			       echo "Impossible d'écrire dans le fichier ($filename)";
			       exit;
			    }
			    
			    //echo "L'écriture de ($somecontent) dans le fichier ($filename) a réussi";
			    
			    fclose($handle);
			                    
			} else {
			    echo "Le fichier $filename n'est pas accessible en écriture.";
			}	
	}
	
	function getSeparateurFromCode($code){
		switch ($code) {
			case 0 : 
				return "," ;
				break;
			case 1 : 
				return  ";" ;
				break;
			case 2 : 
				return "|" ;
				break;
			case 3 : 
				return "\t" ;
				break;
		}		
	}
	
?> 