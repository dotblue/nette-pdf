<?php declare(strict_types=1);

namespace DotBlue\Mpdf;

use Mpdf;
use Nette;


class DocumentFactory
{

	use Nette\SmartObject;

	/** @var array */
	private $customFonts;

	/** @var string */
	private $tempDir;

	/** @var string */
	private $templateDir;

	/** @var array */
	private $defaults = [
		'encoding' => 'utf-8',
		'fonts' => [],
		'img_dpi' => 120,
		'format' => 'A4',
		'margin' => [
			'left' => 0,
			'right' => 0,
			'top' => 0,
			'bottom' => 0,
		],
	];

	/** @var array[] */
	private $themes = [];

	/** @var Nette\Application\UI\ITemplateFactory */
	private $templateFactory;



	public function __construct(
		string $tempDir,
		string $templateDir,
		array $defaults,
		array $customFonts,
		Nette\Application\UI\ITemplateFactory $templateFactory
	)
	{
		$this->customFonts = $customFonts;
		$this->tempDir = $tempDir;
		$this->templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR);
		$this->defaults = array_replace_recursive($this->defaults, $defaults);
		$this->templateFactory = $templateFactory;
	}



	public function addTheme(string $name, array $setup): void
	{
		$this->themes[$name] = array_replace_recursive($this->defaults, $setup);
	}



	public function createPdf(string $theme, string $variant = 'default.latte', array $setup = []): Document
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



	private function createThemedMpdf(string $theme, array $setup = []): Mpdf\Mpdf
	{
		if (!isset($this->themes[$theme])) {
			throw new UnknownThemeException("Theme '$theme' isn't registered.");
		}

		$setup = array_replace_recursive($this->themes[$theme], $setup);

		$configuration = [
			'mode' => $setup['encoding'],
			'format' => $setup['format'],
			'margin_left' => $setup['margin']['left'],
			'margin_right' => $setup['margin']['right'],
			'margin_top' => $setup['margin']['top'],
			'margin_bottom' => $setup['margin']['bottom'],
			'showImageErrors' => TRUE,
			'img_dpi' => $setup['img_dpi'],
			'tempDir' => $this->tempDir,
		];

		if (isset($this->customFonts['fontsDirs'])) {
			$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
			$defaultFontDirs = $defaultConfig['fontDir'];

			$configuration['fontDir'] = array_merge($defaultFontDirs, $this->customFonts['fontsDirs']);
		}

		if (isset($this->customFonts['fonts'])) {
			$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
			$defaultFontData = $defaultFontConfig['fontdata'];

			$configuration['fontdata'] = array_replace($defaultFontData, $this->customFonts['fonts']);
			$configuration['default_font'] = key($this->customFonts['fonts']);
		}

		return new Mpdf\Mpdf($configuration);
	}

}
