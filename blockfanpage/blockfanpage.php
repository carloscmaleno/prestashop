<?php
/*
	Bloque Fan Facebook - PrestaShop
	Author: Carlos Coronado
	Version: 0.1
*/
class BlockFanPage extends Module
{
	function __construct()
	{
		$this->name = 'blockfanpage';
		$this->tab = 'Blocks';
		$this->version = 0.1;
		$this->author= 'Carlos Coronado';

		parent::__construct(); // The parent construct is required for translations

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Bloque de Fans de Facebook');
		$this->description = $this->l('Añade un bloque de Fans de Facebook a la página');
	}

	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('rightColumn') OR !$this->registerHook('leftColumn'))
			return false;
		return true;
	}

	
	function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('guardar'))
		{
			Configuration::updateValue('SOCIAL_FACEBOOK_ACCOUNT', ($a=Tools::getValue('facebook')));
			Configuration::updateValue('SOCIAL_TWITTER_ACCOUNT', ($b=Tools::getValue('twitter')));
			Configuration::updateValue('SOCIAL_TUENTI_ACCOUNT', ($c=Tools::getValue('tuenti')));
			Configuration::updateValue('SOCIAL_GPLUS_ACCOUNT', ($d=Tools::getValue('gplus')));
			Configuration::updateValue('SOCIAL_LINKEDIN_ACCOUNT', ($e=Tools::getValue('linkedin')));
			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmacion').'" />'.$this->l('Ajustes Actualizados Ancho').'</div>';
		}
		return $output.$this->displayForm();
	}
	
	public function displayForm()
	{
		$output =
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Configuración').'</legend>
		<label>'.$this->l('Cuenta de Facebook').'</label>
		<div class="margin-form">
		<input type="text" name="facebook" value="'.Tools::getValue('facebook', Configuration::get('SOCIAL_FACEBOOK_ACCOUNT')).'" />
		</div>
		<label>'.$this->l('Cuenta de Twitter').'</label>
		<div class="margin-form">
		<input type="text" name="twitter" value="'.Tools::getValue('twitter', Configuration::get('SOCIAL_TWITTER_ACCOUNT')).'" />
		</div>
		<label>'.$this->l('Cuenta de Tuenti').'</label>
		<div class="margin-form">
		<input type="text" name="tuenti" value="'.Tools::getValue('tuenti', Configuration::get('SOCIAL_TUENTI_ACCOUNT')).'" />
		</div>
		<label>'.$this->l('Cuenta de LinkedIn').'</label>
		<div class="margin-form">
		<input type="text" name="linkedin" value="'.Tools::getValue('linkedin', Configuration::get('SOCIAL_LINKEDIN_ACCOUNT')).'" />
		</div>
		<label>'.$this->l('Cuenta de Google+').'</label>
		<div class="margin-form">
		<input type="text" name="gplus" value="'.Tools::getValue('gplus', Configuration::get('SOCIAL_GPLUS_ACCOUNT')).'" />
		</div>
		<center><input type="submit" name="guardar" value="'.$this->l('Salvar').'" class="button" /></center>
		</fieldset>
		</form>';
		return $output;
	
	}
	
	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookRightColumn($params)
	{
		global  $smarty;
		
		$smarty->assign("blockfanpage_img",_MODULE_DIR_.$this->name.'/img/');
		$smarty->assign("facebook",$a=Configuration::get("SOCIAL_FACEBOOK_ACCOUNT"));
		$smarty->assign("twitter",$b=Configuration::get("SOCIAL_TWITTER_ACCOUNT"));
		$smarty->assign("tuenti",$c=Configuration::get("SOCIAL_TUENTI_ACCOUNT"));
		$smarty->assign("gplus",$d=Configuration::get("SOCIAL_GPLUS_ACCOUNT"));
		$smarty->assign("linkedin",$e=Configuration::get("SOCIAL_LINKEDIN_ACCOUNT"));
		return $this->display(__FILE__, 'blockfanpage.tpl');
	}

	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

}

?>