<?php
/*
* 2012 Informática Cano
*  @author Informática Cano <atcliente@cano.net>
*  @copyright  2012 Informática Cano Granada S.L.
*  @version  0.1
*  @license    private
*/

if (!defined('_PS_VERSION_'))
	exit;
	
class BlockCopyRight extends Module
{
	
	public function __construct()
	{
		$this->name = 'blockcopyright';
		$this->tab = 'cano';
		$this->version = 0.1;
		$this->author = 'Informática Cano';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Bloque Copyright');
		$this->description = $this->l('<description><![CDATA[Añade un bloque de copyright al pie de la página]]></description>');

	}

	public function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('footer'))
			return false;
		return true;
	}

	public function hookFooter($params)
	{
		return $this->display(__FILE__, 'blockcopyright.tpl');
	}


}


