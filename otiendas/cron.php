<?php

# Options
# @url_param taxes=on (on,off) 
# @url_param storetaxes=on (on,off) 
# @url_param discount=on (on,off) 
# @url_param add_vat=off (on,off) 
# @url_param vat_value=24 (VAT_VALUE) 
# @url_param shipping=off (on,off) 
# @url_param add_tagging=on (on,off) 
# @url_param tagging_params=&utm_source=shopmania&utm_medium=cpc&utm_campaign=direct_link (TAGGING_PARAMS) 
# @url_param description=on (on,off) 
# @url_param image=on (on,off) 
# @url_param specialprice=on (on,off) 
# @url_param sef=off (on,off) 
# @url_param on_stock=off (on,off) 
# @url_param forcepath=off (on,off) 
# @url_param forcefolder= (FORCEFOLDER) 
# @url_param currency= (DEFAULT_CURRENCY) 

// Current datafeed script version
$script_version = "1.25";

// Print current Script version
if (@$_GET['get'] == "version") {
	echo "<b>Datafeed PrestaShop</b><br />";
	echo "script version <b>" . $script_version . "</b><br />";
	exit;
}

if (@!ini_get("safe_mode")) {
    @set_time_limit(0);
}
@ignore_user_abort();
@error_reporting(E_ALL^E_NOTICE);
$_SVR = array();

##### Include configuration files ################################################

$site_base_path = '/home/umi1/public_html/tienda/';
$url='http://www.umigranada.com';

# Once all is set up you need to check the result and make sure the output is correct
# Point the browser to http://www.example.com/path_to_datafeed/shopmania_datafeed_prestashop.php and look into the source code of the out put
# What you need to see is something like this
# Category | Manufacturer | Part Number | Merchant Code | Product Name | Product Description | Product URL | Product Image URL | Product Price | Currency | Shipping value | Availability | GTIN (UPC/EAN/ISBN) 

//Avoid any modifications below this line

// Include configuration file
if(!file_exists($site_base_path . 'init.php')) {
	exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that datafeed_shopmania_prestashop.php is in the root directory, or make sure the path to the directory where the init.php file is located is defined corectly above in $site_base_path variable</BODY></HTML>');
}
else {
	include($site_base_path . 'config/config.inc.php');
	require_once($site_base_path . 'init.php');
}

####################################################################

// Datafeed specific settings
$datafeed_separator = "|"; // Possible options are \t or |

##### Extract params from url ################################################

$apply_taxes = (@$_GET['taxes'] == "off") ? "off" : "on";
$apply_storetaxes = (@$_GET['storetaxes'] == "off") ? "off" : "on";
$apply_discount = (@$_GET['discount'] == "off") ? "off" : "on";
$add_vat = (@$_GET['add_vat'] == "on") ? "on" : "off";
$vat_value = (@$_GET['vat_value'] > 0) ? ((100 + $_GET['vat_value']) / 100) : 1.24; // default value
$add_shipping = (@$_GET['shipping'] == "on") ? "on" : "off";
$availability = (@$_GET['availability'] == "off") ? "off" : "on";
$gtin = (@$_GET['gtin'] == "off") ? "off" : "on";
$add_tagging = (@$_GET['add_tagging'] == "off") ? "off" : "on";
$tagging_params = (@$_GET['tagging_params'] != "") ? urldecode($_GET['tagging_params']) : "utm_source=shopmania&utm_medium=cpc&utm_campaign=direct_link";
$show_description = (@$_GET['description'] == "off") ? "off" : ((@$_GET['description'] == "limited") ? "limited" : ((@$_GET['description'] == "short") ? "short" : ((@$_GET['description'] == "full") ? "full" : ((@$_GET['description'] == "full_limited") ? "full_limited" : "on"))));
$show_image = (@$_GET['image'] == "off") ? "off" : ((@$_GET['image'] == "v3") ? "v3" : ((@$_GET['image'] == "v2") ? "v2" : "on"));
$show_specialprice = (@$_GET['specialprice'] == "off") ? "off" : "on";
$sef = (@$_GET['sef'] == "on") ? "on" : ((@$_GET['sef'] == "v2") ? "v2" : "off");
$on_stock_only = (@$_GET['on_stock'] == "on") ? "on" : "off";
$currency = (@$_GET['currency'] != "") ? $_GET['currency'] : "";
$currency_id = (@$_GET['currency_id'] != "") ? $_GET['currency_id'] : "";
$language_code = (@$_GET['language'] != "") ? $_GET['language'] : "";
$language_id = (@$_GET['language_id'] != "") ? $_GET['language_id'] : "";
$force_path = (@$_GET['forcepath'] == "on") ? "on" : "off";
$force_folder = (@$_GET['forcefolder'] != "") ? $_GET['forcefolder'] : "";
$limit = (@$_GET['limit'] != "") ? $_GET['limit'] : "";
$cookies = (@$_GET['cookies'] == "off") ? "off" : "on";
$default_cat = (@$_GET['default_cat'] == "on") ? "on" : "off";
$display_currency = (@$_GET['display_currency'] != "") ? $_GET['display_currency'] : "";

####################################################################

$base_dir = $url. __PS_BASE_URI__;
$base_image_dir = $url. _THEME_PROD_DIR_;

// Get allow out of stock ordering value 1 - yes / 0 - no
$allow_out_of_stock_ordering = intval(Configuration::get('PS_ORDER_OUT_OF_STOCK'));

// Get stock management condition 
$enable_stock_management = intval(Configuration::get('PS_STOCK_MANAGEMENT'));

if ($enable_stock_management == 1) {
	if ($on_stock_only == "on") {
		$stock_cond = "AND quantity > 0";
	}
	/*elseif ($allow_out_of_stock_ordering == 1) {
		$stock_cond = "AND (quantity >= 0 AND out_of_stock > 0)";
	}
	else {
		$stock_cond = "AND (quantity > 0 OR (quantity='0' AND out_of_stock='1'))";
	}*/
}
else {
	// All products are in stock
	$stock_cond = "";
}

// Print URL options
if (@$_GET['get'] == "options") {
	$script_basepath = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	
	echo "<b>Datafeed PrestaShop</b><br />";
	echo "script version <b>" . $script_version . "</b><br /><br /><br />";
		
	//echo "Taxes options - possible values on, off default value on<br />";
	//echo "taxes=on (on,off) <a href=\"" . $script_basepath . "?taxes=off" . "\" >" . $script_basepath . "?taxes=off" . "</a><br /><br />";
	
	//echo "Store taxes options - possible values on, off default value on<br />";
	//echo "storetaxes = on (on,off) <a href=\"" . $script_basepath . "?storetaxes=off" . "\" >" . $script_basepath . "?storetaxes=off" . "</a><br /><br />";
	
	//echo "Discount options - possible values on, off default value on<br />";
	//echo "discount=on (on,off) <a href=\"" . $script_basepath . "?discount=off" . "\" >" . $script_basepath . "?discount=off" . "</a><br /><br />";
	
	echo "<b>Add VAT to prices</b> - possible values on, off default value off<br />";
	echo "add_vat=off (on,off) <a href=\"" . $script_basepath . "?add_vat=on" . "\" >" . $script_basepath . "?add_vat=on" . "</a><br /><br />";
	
	echo "<b>VAT value</b> - possible values percent value default value 24  - interger or float number ex 19 or 19.5<br />";
	echo "vat_value=24 (VAT_VALUE) <a href=\"" . $script_basepath . "?add_vat=on&vat_value=19" . "\" >" . $script_basepath . "?add_vat=on&vat_value=19" . "</a><br /><br />";
	
	echo "<b>Add shipping to datafeed</b> - possible values on, off default value off - works for a limited number of products (2500-3000)<br />";
	echo "shipping=off (on,off) <a href=\"" . $script_basepath . "?shipping=on" . "\" >" . $script_basepath . "?shipping=on" . "</a><br /><br />";
	
	echo "<b>Add availability to datafeed</b> - possible values on, off default value on<br />";
	echo "availability=on (on,off) <a href=\"" . $script_basepath . "?availability=off" . "\" >" . $script_basepath . "?availability=off" . "</a><br /><br />";
	
	echo "<b>Add GTIN to datafeed</b> - possible values on, off default value on<br />";
	echo "gtin=on (on,off) <a href=\"" . $script_basepath . "?gtin=off" . "\" >" . $script_basepath . "?gtin=off" . "</a><br /><br />";
	
	echo "<b>Add GA Tagging to product URL</b> - possible values on, off default value on<br />";
	echo "add_tagging=on (on,off) <a href=\"" . $script_basepath . "?add_tagging=off" . "\" >" . $script_basepath . "?add_tagging=off" . "</a><br /><br />";
	
	echo "<b>Add custom tagging to product URL</b> - possible values url_encode(TAGGING_PARAMS) default value tagging_params=utm_source=shopmania&utm_medium=cpc&utm_campaign=direct_link<br />";
	echo "tagging_params=utm_source=shopmania&utm_medium=cpc&utm_campaign=direct_link (TAGGING_PARAMS) <a href=\"" . $script_basepath . "?tagging_params=from%3Dshopmania" . "\" >" . $script_basepath . "?tagging_params=from%3Dshopmania" . "</a><br /><br />";

	echo "<b>Display Description options</b> - possible values on, off, short, full, limited, full_limited default value on<br />";
	echo "<ul><li><b>description=off</b> - do not display descriptions<br/ > <a href=\"" . $script_basepath . "?description=off" . "\" >" . $script_basepath . "?description=off" . "</a></li>";
	echo "<li><b>description=short</b> - display short descriptions<br/ > <a href=\"" . $script_basepath . "?description=short" . "\" >" . $script_basepath . "?description=short" . "</a></li>";
	echo "<li><b>description=full</b> - display full descriptions (short description + description)<br/ > <a href=\"" . $script_basepath . "?description=full" . "\" >" . $script_basepath . "?description=full" . "</a></li>";
	echo "<li><b>description=limited</b> - display limited descriptions (descriptions limited to 300 chars)<br/ > <a href=\"" . $script_basepath . "?description=limited" . "\" >" . $script_basepath . "?description=limited" . "</a></li>";
	echo "<li><b>description=full_limited</b> - display full descriptions (short description + description) limited to 300 chars<br/ > <a href=\"" . $script_basepath . "?description=full_limited" . "\" >" . $script_basepath . "?description=full_limited" . "</a></li></ul>";
	
	echo "<b>Display image options</b> - possible values on, off, v2, v3 default value on<br />";
	echo "<ul><li><b>image=off</b> - do not display images<br/ > <a href=\"" . $script_basepath . "?image=off" . "\" >" . $script_basepath . "?image=off" . "</a></li>";
	echo "<li><b>image=v2</b> - /image_id/seo_keyword.jpg<br/ > <a href=\"" . $script_basepath . "?image=v2" . "\" >" . $script_basepath . "?image=v2" . "</a></li>";
	echo "<li><b>image=v3</b> - img/p/x/y/z/xyz.jpg <br/ > <a href=\"" . $script_basepath . "?image=v3" . "\" >" . $script_basepath . "?image=v3" . "</a></li></ul>";
	
	//echo "Special price options - possible values on, off default value on<br />";
	//echo "specialprice=on (on,off) <a href=\"" . $script_basepath . "?specialprice=off" . "\" >" . $script_basepath . "?specialprice=off" . "</a><br /><br />";
	
	echo "<b>Show only on stock products</b> - possible values on, off default value off<br />";
	echo "on_stock=off (on,off) <a href=\"" . $script_basepath . "?on_stock=on" . "\" >" . $script_basepath . "?on_stock=on" . "</a><br /><br />";
	
	echo "<b>Show SEO friendly url</b> - possible values on, off default value off<br />";
	echo "sef=off (on,off,v2) <ul><li><a href=\"" . $script_basepath . "?sef=on" . "\" >" . $script_basepath . "?sef=on" . "</a></li>
	<li><a href=\"" . $script_basepath . "?sef=v2" . "\" >" . $script_basepath . "?sef=v2" . "</a> (used in prestashop versions 1.4.1 or higher)</li></ul>";
	
	echo "<b>Get prices in specified currency</b> - possible values USD,EUR etc. <br />";
	echo "currency=DEFAULT_CURRENCY <a href=\"" . $script_basepath . "?currency=EUR" . "\" >" . $script_basepath . "?currency=EUR" . "</a><br /><br />";
	
	echo "<b>Get prices in specified currency id</b> - possible values 1,2 etc. <br />";
	echo "currency_id=DEFAULT_CURRENCY_ID <a href=\"" . $script_basepath . "?currency_id=1" . "\" >" . $script_basepath . "?currency_id=1" . "</a><br /><br />";
		
	echo "<b>Get texts in specified language code</b> - possible values en,ro etc. <br />";
	echo "language=DEFAULT_LANGUAGE_CODE <a href=\"" . $script_basepath . "?language=en" . "\" >" . $script_basepath . "?language=en" . "</a><br /><br />";
	
	echo "<b>Get texts in specified language id</b> - possible values 1,2 etc. <br />";
	echo "language_id=DEFAULT_LANGUAGE_ID <a href=\"" . $script_basepath . "?language_id=1" . "\" >" . $script_basepath . "?language_id=1" . "</a><br /><br />";
	
	echo "<b>Limit displayed products</b> - possible values integer (start,step)<br />";
	echo "limit=no_limit <a href=\"" . $script_basepath . "?limit=0,10" . "\" >" . $script_basepath . "?limit=0,10" . "</a><br /><br />";

	echo "<b>Use cookies</b> - possible values on, off default value on - used to rewrite language and currency cookies with selected values when displaing in certain language or currency<br />";
	echo "cookies=on (on,off) <a href=\"" . $script_basepath . "?cookies=off" . "\" >" . $script_basepath . "?cookies=off" . "</a><br /><br />";
	
	echo "<b>Use default categories</b> - use products default category possible values on, off default value off<br />";
	echo "default_cat=off (on,off) <a href=\"" . $script_basepath . "?default_cat=on" . "\" >" . $script_basepath . "?default_cat=on" . "</a><br /><br />";
	
	echo "<b>Display currency code</b> - force the display of certain currency code possible values USD,EUR etc. <br />";
	echo "display_currency=DEFAULT_CURRENCY <a href=\"" . $script_basepath . "?display_currency=EUR" . "\" >" . $script_basepath . "?display_currency=EUR" . "</a><br /><br />";
		
	echo "<br />";
	
	exit;
	
}

##### Extract options from database ################################################

// Get default currency
$res_default_currency = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE name = "PS_CURRENCY_DEFAULT"');
$default_currency_id = ($res_default_currency[0]['value'] > 0) ? $res_default_currency[0]['value'] : 1;

// Use selected currency id
if ($currency_id > 0) {
	$res_currency = Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "currency WHERE id_currency = '" . addslashes($currency_id) . "'");
	$CURRENCY['id_currency'] = $res_currency[0]['id_currency'];
	$CURRENCY['iso_code'] = $res_currency[0]['iso_code'];
	$CURRENCY['conversion_rate'] = $res_currency[0]['conversion_rate'];
	unset($res_currency);
}
elseif ($currency != "") {
	// Use selected currency code
	$res_currency = Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "currency WHERE iso_code = '" . addslashes($currency) . "' AND deleted = 0");
	$CURRENCY['id_currency'] = $res_currency[0]['id_currency'];
	$CURRENCY['iso_code'] = $res_currency[0]['iso_code'];
	$CURRENCY['conversion_rate'] = $res_currency[0]['conversion_rate'];
	$CURRENCY['conversion_rate'] = ($CURRENCY['conversion_rate'] > 0) ? $CURRENCY['conversion_rate'] : 1;
}
else {
	$res_currency = Db::getInstance()->ExecuteS("SELECT * FROM " . _DB_PREFIX_ . "currency WHERE id_currency = '" . addslashes($default_currency_id) . "'");
	$CURRENCY['id_currency'] = $res_currency[0]['id_currency'];
	$CURRENCY['iso_code'] = $res_currency[0]['iso_code'];
	$CURRENCY['conversion_rate'] = 1;
	
	unset($res_default_currency);
	unset($res_currency);
}

$CURRENCY['iso_code'] = (trim(strtolower($CURRENCY['iso_code'])) == "lei" || trim(strtolower($CURRENCY['iso_code'])) == "ro") ? "RON" : $CURRENCY['iso_code'];

// Force displayed currency
$datafeed_currency = ($display_currency != "") ? $display_currency : $CURRENCY['iso_code'];

// Get lang id
if ($language_id > 0) {
	// Set the main language
	$main_language = $language_id;
}
elseif ($language_code != "") {

	// Detect specified  language ID
	$res_language_id = Db::getInstance()->ExecuteS("SELECT id_lang FROM " . _DB_PREFIX_ . "lang WHERE iso_code = '" . addslashes($language_code) . "'");
		
	// Set the main language
	$main_language = $res_language_id[0]['id_lang'];
	$main_language_code = $language_code;
}
else {
	// Detect default  language ID
	$res_default_lang = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE name = "PS_LANG_DEFAULT"');
	$main_language = ($res_default_lang[0]['value'] > 0) ? $res_default_lang[0]['value'] : 1;
	
	$default_lang = $main_language;
}

// Detect specified  language ID
$language_code_res = Db::getInstance()->ExecuteS("SELECT iso_code FROM " . _DB_PREFIX_ . "lang WHERE id_lang = '" . addslashes($main_language) . "'");
// Get main language_code
$main_language_code = $language_code_res[0]['iso_code'];

// Rewrite cookies with selected values
if ($cookies == "on") {
	// Set main language
	$cookie->id_lang = $main_language;
	// Rewrite currency cookie with selected value
	$cookie->id_currency = $default_currency_id;
}

// Add shopping cart
if ($add_shipping == "on") {
	global $cart;
	$cart->add();
}

// Build limit filter
if ($limit != "") {
	$limit_arr = explode(",", $limit);
	$limit_cond = ($limit_arr[0] >= 0 && @$limit_arr[1] > 0) ? " LIMIT " . $limit_arr[0] . "," . @$limit_arr[1] : (($limit_arr[0] > 0) ? " LIMIT 0," . $limit_arr[0] : "");
}
else {
	$limit_cond = "";
}

######################################################################


##### Extract products from database ###############################################

// Extract product id, category id
$r = Db::getInstance()->ExecuteS("SELECT p.id_product, " . _DB_PREFIX_ . "category_product.id_category FROM " . _DB_PREFIX_ . "product p 
LEFT JOIN " . _DB_PREFIX_ . "category_product ON " . _DB_PREFIX_ . "category_product.id_product = p.id_product
LEFT JOIN " . _DB_PREFIX_ . "category c ON " . _DB_PREFIX_ . "category_product.id_category = c.id_category
LEFT JOIN " . _DB_PREFIX_ . "category_group cg ON " . _DB_PREFIX_ . "category_product.id_category = cg.id_category
WHERE p.active=1 AND c.active=1 AND cg.id_group=1 AND p.available_for_order=1 " . $stock_cond . "
ORDER BY p.id_product ASC, id_category DESC" . addslashes($limit_cond));
###################################################################


##### Print product data ####################################################

$current_id = 0;

$csv = fopen("products.csv","w");

	foreach($r as $field){

				
		// If we've sent this one, skip the rest - this is to ensure that we do not get duplicate products
		$prod_id = $field['id_product'];
		if ($current_id == $prod_id) {
			continue;
		}
		else {
			$current_id = $prod_id;
		}
		
		// Get product data
		$product = new Product(intval($field['id_product']), true, intval($main_language));
		
		$item = array();
		$item['product_id'] = $product->id;
		$item['manufacturer_name'] = $product->manufacturer_name;
		$item['name'] = $product->name;
		$item['link_rewrite'] = $product->link_rewrite;
		$item['image'] = $product->getCover($product->id);
		$item['mpn'] = $product->reference;
		
		$item['price'] = floatval(trim($product->getPrice(true, NULL, 2)));
		$item['price'] = $item['price'] * $CURRENCY['conversion_rate'];
		
		// Add VAT to prices
		if ($add_vat == "on") {
			$item['price'] = $item['price'] * $vat_value;
		}
		
		if ($default_cat == "on") {
			$item['cat_name'] = Tools::getPath($product->id_category_default, '');
		}
		elseif ($field['id_category'] > 0) {
			$item['cat_name'] = Tools::getPath($field['id_category'], '');
		}
		else {
			$category = new Category($product->id_category_default, intval($main_language));
			$item['cat_name'] = (isset($category->id) AND $category->id) ? Tools::getPath($category->id, '') : Tools::getPath($product->id_category_default, '');
		}
		$item['cat_name'] = trim(smfeed_html_to_text($item['cat_name']));
		
		// Clean product name (new lines)
		$item['name'] = str_replace("\n", "", strip_tags($item['name']));
		
		$item['description'] = "";
		
		// Limit description size
		if ($show_description == "limited" || $show_description == "full_limited") {
			$item['description'] = ($show_description == "limited") ? $product->description : ($product->description_short . " " . $product->description);
			$item['description'] = substr($item['description'], 0, 400);
			$item['description'] = smfeed_replace_not_in_tags("\n", "<BR />", $item['description']);
			$item['description'] = str_replace("\n", " ", $item['description']);
			$item['description'] = str_replace("\r", "", $item['description']);
			$item['description'] = strip_tags($item['description']);
			$item['description'] = substr($item['description'], 0, 300);
		}
		else {
			if ($show_description == "on") {
				// Get description
				$item['description'] = $product->description;
			}
			elseif ($show_description == "short") {
				// Get short description
				$item['description'] = $product->description_short;
			}
			elseif ($show_description == "full") {
				// Get full description (shortdesc + desc)
				$item['description'] = $product->description_short . " " . $product->description;
			}

			$item['description'] = smfeed_replace_not_in_tags("\n", "<BR />", $item['description']);
			$item['description'] = str_replace("\n", " ", $item['description']);
			$item['description'] = str_replace("\r", "", $item['description']);
		}
		
		// Clean product names and descriptions (separators)
		if ($datafeed_separator == "\t") {
			$item['name'] = str_replace("\t", " ", strip_tags($item['name']));
			$item['description'] = str_replace("\t", " ", $item['description']);
			$item['cat_name'] = str_replace("\t", ">", $item['cat_name']);
		}
		elseif ($datafeed_separator == "|") {
			$item['name'] = str_replace("|", " ", strip_tags($item['name']));
			$item['description'] = str_replace("|", " ", $item['description']);
			$item['cat_name'] = str_replace("|", ">", $item['cat_name']);
		}
		else {
			print "Incorrect columns separator.";
			exit;			
		}
				
		$use_lang_code = ($default_lang == $main_language) ? "" : $main_language_code;
		
		if ($sef == "off") {
			$item['prod_url'] = smfeed_get_product_url($item['product_id'], "", $base_dir, $use_lang_code);
		}
		elseif($sef == "v2") {
			if((int)(Configuration::get('PS_REWRITING_SETTINGS'))) {
				$rewrite_infos = Product::getUrlRewriteInformations((int)$field['id_product']);
				
				foreach ($rewrite_infos AS $infos) {
					if ($infos['id_lang'] == $main_language){
						$item['prod_url'] = $link->getProductLink((int)$field['id_product'], $infos['link_rewrite'], $infos['category_rewrite'], $infos['ean13'], (int)$infos['id_lang']);
					}
				}
			}
		}
		else {
			$item['prod_url'] = smfeed_get_product_url($item['product_id'], $item['link_rewrite'], $base_dir, $use_lang_code);		
		}
		
		// Add GA Tagging parameters to url
		if ($add_tagging == "on") {
			$and_param = (preg_match("/\?/", $item['prod_url'])) ? "&" : "?";
			$item['prod_url'] = $item['prod_url'] . $and_param . $tagging_params;
		}
		
		if ($item['image']['id_image'] > 0 && $show_image != "off") {
			$item['prod_image'] = smfeed_get_product_image($item['product_id'], $item['image']['id_image'], $base_image_dir, $sef, $show_image, $base_dir, $item['link_rewrite']);		
		}
		else {
			$item['prod_image'] = "";
		}
		
		// Display availability 		
		if ($availability == "on") {
			$row['availability'] = ($product->checkQty(1) == 1) ? "In stock" : "Out of stock";
		}
		else { 
			$row['availability'] = "";
		}
			
		// Display shipping
		if ($add_shipping == "on") {
			// Add product to cart
			$updateQuantity = $cart->updateQty((int)(1), (int)($product->id), (int)($idProductAttribute), $customizationId, Tools::getValue('op', 'up'));
	
			// Get shipping value
			$row['shipping_value'] = $cart->getOrderShippingCost();
			$row['shipping_value'] = $row['shipping_value'] * $CURRENCY['conversion_rate'];
			
			// Delete product from cart 		
			$product->deleteCartProducts();	
		}
		else {
			$row['shipping_value'] = "";
		}
		
		// Display GTIN 
		$item['gtin'] = ($gtin == "on") ? (($product->upc != "") ? $product->upc : (($product->ean13 != "") ? $product->ean13 : "")) : "";
			
		// Required fields are: category name, merchant product ID, product name, product URL, product price
		// For the product model you should only use the manufacturer code, ISBN code or UPC code - If you are not sure about a field please leave it empty
		
		// Output the datafeed content
		// Category, Manufacturer, Model, ProdCode, ProdName, ProdDescription, ProdURL, ImageURL, Price, Currency, Shipping value, Availability, GTIN (UPC/EAN/ISBN) 
		
		$text=  
		$item['cat_name'] . $datafeed_separator . 
		$item['manufacturer_name'] . $datafeed_separator . 
		$item['mpn'] . $datafeed_separator . 
		$item['product_id'] . $datafeed_separator . 
		$item['name'] . $datafeed_separator . 
		$item['description'] . $datafeed_separator . 
		$item['prod_url'] . $datafeed_separator . 
		$item['prod_image'] . $datafeed_separator .
		$item['price'] . $datafeed_separator . 
		$datafeed_currency . $datafeed_separator . 
		$row['shipping_value'] . $datafeed_separator .
		$row['availability'] . $datafeed_separator . 
		$item['gtin'] . "\n";
		fwrite($csv,$text);
}
fclose($csv);
echo 'ok';
###################################################################


##### Functions ########################################################

// Function to return the Product URL based on your product ID
function smfeed_get_product_url($prod_id, $link_rewrite, $base_dir, $use_lang){	
	
	$url_lang = ($use_lang != "") ? "lang-" . $use_lang . "/" : "";
	
	if ($link_rewrite != "") {		
		return $base_dir . $url_lang . $prod_id . "-" . $link_rewrite . ".html";
	}
	else {
		return $base_dir . $url_lang . "product.php?id_product=" . $prod_id;
	}
}

// Function to return the Product Image based on your product image or optionally Product ID
function smfeed_get_product_image($prod_id, $prod_image, $base_image_dir, $sef, $show_image, $base_dir, $link_rewrite){
	
	if ($show_image == "v2") {
		return $base_dir . $prod_image . "/" . $link_rewrite . ".jpg";
	}
	elseif ($show_image == "v3") {
		$tmp = str_split($prod_image);
		$image_folder = join("/", $tmp);
		return $base_image_dir . $image_folder . "/" . $prod_image . ".jpg";
	}
	elseif ($sef == "v2") {
		return $base_dir . $prod_id . "-" . $prod_image . "/" . $link_rewrite . ".jpg";
	}
	else {
		return $base_image_dir . $prod_id . "-" . $prod_image . ".jpg";
	}
	
}

function smfeed_html_to_text($string){

	$search = array (
		"'<script[^>]*?>.*?</script>'si",  // Strip out javascript
		"'<[\/\!]*?[^<>]*?>'si",  // Strip out html tags
		"'([\r\n])[\s]+'",  // Strip out white space
		"'&(quot|#34);'i",  // Replace html entities
		"'&(amp|#38);'i",
		"'&(lt|#60);'i",
		"'&(gt|#62);'i",
		"'&(nbsp|#160);'i",
		"'&(iexcl|#161);'i",
		"'&(cent|#162);'i",
		"'&(pound|#163);'i",
		"'&(copy|#169);'i",
		"'&(reg|#174);'i",
		"'&#8482;'i",
		"'&#149;'i",
		"'&#151;'i"
		);  // evaluate as php
	
	$replace = array (
		" ",
		" ",
		"\\1",
		"\"",
		"&",
		"<",
		">",
		" ",
		"&iexcl;",
		"&cent;",
		"&pound;",
		"&copy;",
		"&reg;",
		"<sup><small>TM</small></sup>",
		"&bull;",
		"-",
		);
	
	$text = preg_replace ($search, $replace, $string);
	return $text;
	
}

function smfeed_clean_description($string){

	$search = array (
		"'<html>'i",
		"'</html>'i",
		"'<body>'i",
		"'</body>'i",
		"'<head>.*?</head>'si",
		"'<!DOCTYPE[^>]*?>'si"
		); 

	$replace = array (
		"",
		"",
		"",
		"",
		"",
		""
		); 
		
	$text = preg_replace ($search, $replace, $string);
	return $text;

}

function smfeed_replace_not_in_tags($find_str, $replace_str, $string) {
	
	$find = array($find_str);
	$replace = array($replace_str);	
	preg_match_all('#[^>]+(?=<)|[^>]+$#', $string, $matches, PREG_SET_ORDER);	
	foreach ($matches as $val) {	
		if (trim($val[0]) != "") {
			$string = str_replace($val[0], str_replace($find, $replace, $val[0]), $string);
		}
	}	
	return $string;
}

###################################################################

?>