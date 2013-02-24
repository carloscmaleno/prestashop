<?php
/*
	Bloque Fan Facebook - PrestaShop
	Author: Carlos Coronado
	Version: 0.1
*/
class BlockGTalkAssistence extends Module
{
	function __construct()
	{
		$this->name = 'blockgtalkassistence';
		$this->tab = 'Blocks';
		$this->version = 0.1;
		$this->author= 'Carlos Coronado';

		parent::__construct(); // The parent construct is required for translations

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Bloque para soporte de Google Talk');
		$this->description = $this->l('Añade un bloque para que los clientes puedan hablar con el personal de la tienda.');
	}

	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('rightColumn') OR !$this->registerHook('leftColumn'))
			return false;
		return true;
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookRightColumn($params)
	{
		return $this->display(__FILE__, 'blockgtalkassistence.tpl');
	}

	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

}

?>