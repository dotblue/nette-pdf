<?php

namespace DotBlue\Mpdf;

use mPDF;
use Nette\Application\UI\ITemplateFactory;
use Nette\Object;

class DocumentFactory extends Object
{

	/** @var string */
	private $templateDir;

	/** @var array */
	private $defaults = [
		'encoding' => 'utf-8',
		'img_dpi' => 120,
		'size' => 'A4',
		'margin' => [
			'left' => 0,
			'right' => 0,
			'top' => 0,
			'bottom' => 0,
		],
	];

	/** @var array[] */
	private $themes = [];

	/** @var ITemplateFactory */
	private $templateFactory;

	/**
	 * @param  string
	 * @param  array
	 * @param  ITemplateFactory
	 */
	public function __construct($templateDir, array $defaults, ITemplateFactory $templateFactory)
	{
		$this->templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR);
		$this->defaults = array_replace_recursive($this->defaults, $defaults);
		$this->templateFactory = $templateFactory;
	}



	/**
	 * Registers new theme.
	 *
	 * @param  string
	 * @param  array
	 */
	public function addTheme($name, array $setup)
	{
		$this->themes[$name] = array_replace_recursive($this->defaults, $setup);
	}



	/**
	 * Creates new PDF.
	 *
	 * @param  string
	 * @param  string|NULL
	 * @param  array|NULL
	 * @return Document
	 */
	public function createPdf($theme, $variant = 'default.latte', array $setup = [])
	{
		$pdf = $this->createThemedMpdf($theme, $setup);

		$template = $this->templateFactory->createTemplate();
		$template->setFile($this->templateDir . '/' . $theme . '/' . $variant);
		$template->dir = $this->templateDir . '/' . $theme;

		$pdf->WriteHTML(file_get_contents($this->templateDir . '/' . $theme . '/style.css'), 1);

		return new Document($pdf, $template);
	}



	/**
	 * @param  string
	 * @param  array|NULL
	 * @return mPDF
	 */
	private function createThemedMpdf($theme, array $setup = [])
	{
		if (!isset($this->themes[$theme])) {
			throw new UnknownThemeException("Theme '$theme' isn't registered.");
		}

		$setup = array_replace_recursive($this->themes[$theme], $setup);

		$mpdf = new mPDF(
			$setup['encoding'],
			$setup['size'],
			'',
			'',
			$setup['margin']['left'],
			$setup['margin']['right'],
			$setup['margin']['top'],
			$setup['margin']['bottom']
		);
		$mpdf->showImageErrors = TRUE;
		$mpdf->img_dpi = $setup['img_dpi'];
		return $mpdf;
	}

}
