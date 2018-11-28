<?php

namespace DotBlue\Mpdf;

use LogicException;
use Mpdf\Mpdf;
use Nette;
use Nette\Application\Application;
use Nette\Utils\Strings;


class DocumentFactory
{

	use Nette\SmartObject;

	/** @var string */
	private $templateDir;

	/** @var array */
	private $defaults = [
		'encoding' => 'utf-8',
		'fonts' => [],
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

	/** @var Application */
	private $application;



	/**
	 * @param  string
	 * @param  array
	 * @param  array
	 * @param  ITemplateFactory
	 */
	public function __construct($templateDir, array $defaults, array $customFonts, ITemplateFactory $templateFactory)
	{
		$this->templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR);
		$this->defaults = array_replace_recursive($this->defaults, $defaults);
		$this->templateFactory = $templateFactory;

		if ($customFonts) {
			if (defined('_MPDF_SYSTEM_TTFONTS_CONFIG')) {
				throw new LogicException("Constant _MPDF_SYSTEM_TTFONTS_CONFIG can't be defined to allow dotblue/nette-pdf to configure fonts.");
			}

			define('_MPDF_SYSTEM_TTFONTS_CONFIG', __DIR__ . '/config_fonts.php');
			global $__dotblueNettePdfFonts;
			$__dotblueNettePdfFonts = $customFonts;
		}
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

		$themeDir = $this->templateDir . '/' . $theme;

		$template = $this->templateFactory->createTemplate();
		$template->setFile($themeDir . '/' . $variant);
		$template->dir = $themeDir;

		$pdf->SetBasePath($themeDir);

		if (is_file($themeDir . '/style.css')) {
			$pdf->WriteHTML(file_get_contents($themeDir . '/style.css'), 1);
		}

		return new Document($pdf, $template);
	}



	/**
	 * @param  string
	 * @param  array|NULL
	 * @return Mpdf
	 */
	private function createThemedMpdf($theme, array $setup = [])
	{
		if (!isset($this->themes[$theme])) {
			throw new UnknownThemeException("Theme '$theme' isn't registered.");
		}

		$setup = array_replace_recursive($this->themes[$theme], $setup);

		$mpdf = new Mpdf([
			'format' => $setup['size'],
			'margin_left' => $setup['margin']['left'],
			'margin_right' => $setup['margin']['right'],
			'margin_top' => $setup['margin']['top'],
			'margin_bottom'=> $setup['margin']['bottom']
		]);
		$mpdf->showImageErrors = TRUE;
		$mpdf->img_dpi = $setup['img_dpi'];
		return $mpdf;
	}

}
