<?php

function dametiempo(){
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		return $mtime;
}

function str2html($str){
		$str=str_replace(array("\n","\r"), '<br/>', $str);				
		$str=str_replace('"', '&quot;', $str); 
		$str=str_replace("'", '&quot;', $str);
		return $str;
}

function str2title ($str){
  $exclude = array("!","@","#","$","%","^","&","*","(",")","=","{","}","[","]","|","\\","/","?","<",">","~","`","_","+","=","-","º","ª","'","Ž",",","\"",";",":",",",".","±");
  $str=str_replace($exclude,"",$str);
  return $str;
}

function str2meta($str){
		$exclude = array("!","@","#","$","%","^","&","*","(",")","=","{","}","[","]","|","\\","/","?","<",">","~","`","_","+","=","-","º","ª","'","Ž",",","\"",";",":",",",".","±");
		$str=str_replace($exclude,"",strtolower($str));
		$str=str_replace(array("Á","á"),"a",$str);
		$str=str_replace(array("É","é"),"e",$str);
		$str=str_replace(array("Í","í"),"i",$str);
		$str=str_replace(array("Ó","ó"),"o",$str);
		$str=str_replace(array("Ú","ú","Ü","ü"),"u",$str);
		$str=str_replace(array("Ñ","ñ"),"ñ",$str);
	  return $str;
	}

function parse_csv($str, $options = null) {
    $delimiter = empty($options['delimiter']) ? "|" : $options['delimiter'];
    $to_object = empty($options['to_object']) ? false : true;
    $lines = explode("\n", $str);
    //pr($lines);
    $field_names = explode($delimiter, array_shift($lines));
    foreach ($lines as $line) {
        // Skip the empty line
        if (empty($line)) continue;
        $fields = explode($delimiter, $line);
        $_res = $to_object ? new stdClass : array();
        foreach ($field_names as $key => $f) {
            if ($to_object) {
                $_res->{$f} = $fields[$key];
            } else {
                $_res[$f] = $fields[$key];
            }
        }
        $res[] = $_res;
    }
    return $res;
}



function str2url($title,$sep = "-"){
	$exclude = array("!","@","#","$","%","^","&","*","(",")","=","{","}","[","]","|","\\","/","?","<",">","~","`","_","+","=","-","º","ª","'",
		"Ž",",","\"","Ì","Ü","Ñ","ñ","Í","í","Á","á","É","é","Ó","ó","Ú","ú",";",":",",",".","±");
	$pieces = explode(' ',str_replace($exclude,"",strtolower($title)));
	$output = NULL;
	foreach($pieces as $key => $val){
		if($pieces[$key+1] == NULL){
			$sep = NULL;
		}
		$output .= $val.$sep;
	}
	return $output;
}

?> 
