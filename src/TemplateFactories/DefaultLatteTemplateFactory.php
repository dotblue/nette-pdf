<?php

namespace DotBlue\Mpdf\TemplateFactories;

use DotBlue\Mpdf\ITemplateFactory;
use Nette;
use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte;


class DefaultLatteTemplateFactory extends Nette\Object implements ITemplateFactory
{

	/** @var ApplicationLatte\TemplateFactory */
	private $templateFactory;



	public function __construct(ApplicationLatte\TemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}



	public function createTemplate(UI\Control $control)
	{
		return $this->templateFactory->createTemplate($control);
	}

}
