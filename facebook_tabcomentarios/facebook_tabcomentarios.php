<?php
/*
** Module name : Facebook - Comentarios de FB en Productos en un TAB
** Ver : 0.2
** Author : Informática Cano Granada
** Website : http://www.cano.net
** e-mail : atcliente@cano.net
** Release date : 25-Julio-2012
*/

class facebook_tabcomentarios extends Module
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'facebook_tabcomentarios';
        $this->tab = 'front_office_features';
        $this->version = '0.2';
		$this->author = 'Informática Cano Granada';

        parent::__construct();

        $this->displayName = $this->l('Añade comentarios de Facebook en los Productos.');
        $this->description = $this->l('Añade comentarios con Facebook en tus productos en una pestaña.');
    }

    function install()
    {
		if (!parent::install())
			return false;
		if(!$this->registerHook('productTab'))
			return false;
		if(!$this->registerHook('productTabContent'))
			return false;
		return true;
    }

    	function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitFbcomentarios'))
		{
			//Actualizar Comentarios en tamaño y numero por pagina
			if (!$nocomentNbr = Tools::getValue('nocomentNbr') OR empty($nocomentNbr))
				$output .= '<div class="alert error">'.$this->l('Usted debe llenar el Campo de No. de comentarios por Página').'</div>';
				if (!$anchoNbr = Tools::getValue('anchoNbr') OR empty($anchoNbr))
				$output .= '<div class="alert error">'.$this->l('Usted debe llenar el campos de ancho de comentarios').'</div>';
			else
			{
				Configuration::updateValue('NO_COMENTARIOS', ($nocomentNbr));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmacion').'" />'.$this->l('Ajustes Actualizados No. Comentarios').'</div>';
				Configuration::updateValue('NO_ANCHO_NBR', ($anchoNbr));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmacion').'" />'.$this->l('Ajustes Actualizados Ancho').'</div>';
				}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		global $cookie;
        $tokenTools = Tools::getAdminToken('AdminTools'.intval(Tab::getIdFromClassName('AdminTools')).intval($cookie->id_employee));
        $tokenPreferences = Tools::getAdminToken('AdminPreferences'.intval(Tab::getIdFromClassName('AdminPreferences')).intval($cookie->id_employee));
		$output =
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('No. de comentarios por pagina').'</label>
				<div class="margin-form">
					<input type="number" size="4" name="nocomentNbr" value="'.Tools::getValue('nocomentNbr', Configuration::get('NO_COMENTARIOS')).'" />
				</div>	
				<label>'.$this->l('Ancho de los comentarios').'</label>
				<div class="margin-form">
					<input type="number" size="5" name="anchoNbr" value="'.Tools::getValue('anchoNbr', Configuration::get('NO_ANCHO_NBR')).'" />
				</div>			
				<center><input type="submit" name="submitFbcomentarios" value="'.$this->l('Salvar').'" class="button" /></center>
			</fieldset>
		</form>
		<div>Puedes moderar los comentarios en: <a href="http://developers.facebook.com/tools/comments" target="_blank">http://developers.facebook.com/tools/comments </a></div>';
		return $output;

    }
	public function hookProductTab($params)
    {
		global $smarty;
		global $cookie;

		//$smarty->assign();
		return ($this->display(__FILE__, '/tabfbcomentarios.tpl'));
	}

    public function hookProductTabContent($params)
	 {
		global $smarty;
		
		$no_Fb_comentarios = Configuration::get('NO_COMENTARIOS');
		$ancho_comentarios = Configuration::get('NO_ANCHO_NBR');
		$smarty->assign('no_Fb_comentarios', $no_Fb_comentarios);
		$smarty->assign('ancho_comentarios', $ancho_comentarios);
		return ($this->display(__FILE__, '/tabcontenidofbcomentarios.tpl'));
	}
}

?>
