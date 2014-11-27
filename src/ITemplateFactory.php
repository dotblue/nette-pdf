<?php

namespace DotBlue\Mpdf;

use Nette\Application\UI;


interface ITemplateFactory
{

	/**
	 * @return UI\ITemplate
	 */
	function createTemplate(UI\Control $control);

}
