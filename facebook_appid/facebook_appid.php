<?php
/*
** Module name : Facebook APP ID
** Ver : 0.1
** Author : Informática Cano Granada
** Website : http://www.cano.net
** e-mail : atcliente@cano.net
** Release date : 25-Octubre-2012
*/

class facebook_appid extends Module
{
    function __construct()
    {
        $this->name = 'facebook_appid';
        $this->tab = 'front_office_features';
        $this->version = '0.1';
		$this->author = 'Informática Cano http://www.cano.net"';
		$this->displayName = 'Facebook APP ID';
		$this->description = 'Añade el script de Facebook para usar sus Aplicaciones en la tienda.';
		parent::__construct();
    }

    function install()
    {
		if (!parent::install())
			return false;
		if(!$this->registerHook('footer'))
			return false;
		if(!$this->registerHook('header'))
			return false;
		return true;
    }
	
	function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitFbAppID'))
		{
			//update facebook app id
			if (!$appNbr = Tools::getValue('appNbr') OR empty($appNbr))
				$output .= '<div class="alert error">'.$this->l('Usted debe llenar el campo de ID de Facebook').'</div>';
			else
			{
				Configuration::updateValue('FACEBOOK_APPID', ($appNbr));
				Configuration::updateValue('FACEBOOK_MODERATOR', $a=Tools::getValue('appAdmin'));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmacion').'" />'.$this->l('Settings actualizados').'</div>';
			}
		}
		return $output.$this->displayForm();
	}

	function displayForm()
	{

  	$output =
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
			<p>1. '.$this->l('Crea tu facebook application, en URL pon la de tu tienda').' : <a href="http://developers.facebook.com/setup/" target="_blank" title="'.$this->l('Click aquí para empezar').'">&nbsp;<span style="font-weight:bold; color:blue; text-decoration:underline;">Setup</span></a></p>
            <p>2. '.$this->l('Copia el ID de Aplicacion o ve a tu Developer Dashboard para copiarlo').' : <a href="http://www.facebook.com/developers/apps.php" target="_blank" title="'.$this->l('Developer dashboard').'">&nbsp;<span style="font-weight:bold; color:blue; text-decoration:underline;">Developer Dashboard</span></a></p><br />
				<label>'.$this->l('Tu Facebook App ID').'</label>
				<div class="margin-form">
					<input type="text" size="10" name="appNbr" value="'.Tools::getValue('appNbr', Configuration::get('FACEBOOK_APPID')).'" />
				</div>				
				<label>'.$this->l('Nombre de usuario del moderador').'</label>
				<div class="margin-form">
					<input type="text" size="10" name="appAdmin" value="'.Tools::getValue('appAdmin', Configuration::get('FACEBOOK_MODERATOR')).'" />
				</div>				
				<center><input type="submit" name="submitFbAppID" value="'.$this->l('Salvar').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}
	
    function hookFooter($params)
    {
		global $smarty;
		
		$id_appFb = Configuration::get('FACEBOOK_APPID');
		$smarty->assign('id_appFb', $id_appFb);
		return ($this->display(__FILE__, 'fb_appid.tpl'));
		
	}
	
	function hookHeader($params){
		global $smarty;
	
		$smarty->assign('fb_appid', Configuration::get('FACEBOOK_APPID'));
		$smarty->assign('fb_appadmin', Configuration::get('FACEBOOK_MODERATOR'));
		//return $smarty->display(__FILE__, 'fb_appid_header.tpl');
	}
}

?>
