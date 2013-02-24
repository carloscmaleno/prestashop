<?php

class BlockSocialButtons extends Module
{
	function __construct()
	{
		$this->name = 'blocksocialbuttons';
		$this->tab = 'Blocks';
		$this->version = 0.1;
		$this->author= 'Carlos Coronado';
		$this->need_instance = 0;
		$this->is_configurable = 0;

		parent::__construct(); // The parent construct is required for translations

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Bloque de botones para compartir');
		$this->description = $this->l('Añade botones en la ficha del producto para compartir en las redes sociales');
	}

	function install()
	{
	 	if (!parent::install() OR !$this->registerHook('extraLeft'))
	 		return false;
		return true;
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookExtraLeft($params)
	{
		return $this->display(__FILE__, 'blocksocialbuttons.tpl');
	}

}

?>