<?php

namespace DotBlue\Mpdf;

use Mpdf\Mpdf;
use Nette;
use Nette\Application\UI;
use Nette\Utils\Image;


class Document
{

	use Nette\SmartObject;

	/** @var Mpdf */
	private $mpdf;

	/** @var UI\ITemplate */
	private $template;



	public function __construct(Mpdf $mpdf, UI\ITemplate $template)
	{
		$this->mpdf = $mpdf;
		$this->template = $template;
	}



	/**
	 * @param  string
	 * @param  string
	 * @return Document provides a fluent interface
	 */
	public function addImageFromPath($name, $path)
	{
		$this->addImage($name, Image::fromFile($path));
		return $this;
	}



	/**
	 * Makes image available in template via "var:$name" notation.
	 *
	 * @param  string
	 * @param  Image
	 * @return Document provides a fluent interface
	 */
	public function addImage($name, Image $image)
	{
		$this->mpdf->$name = $image->toString(Image::PNG);
		return $this;
	}



	/**
	 * Saves document to given destination.
	 *
	 * @param  string
	 */
	public function saveTo($path)
	{
		$this->finalize();
		return $this->mpdf->Output($path, 'F');
	}



	/**
	 * Returns rendered document as string.
	 *
	 * @return string
	 */
	public function render()
	{
		$this->finalize();
		return $this->mpdf->Output('', 'S');
	}


	/**
	 * Forces the document to be downloaded.
	 *
	 * @param  string
	 */
	public function forceDownload($filename)
	{
		$this->finalize();
		return $this->mpdf->Output($filename, 'D');
	}


	/**
	 * Print a PDF file to screen
	 */
	public function printPdf()
	{
		$this->finalize();
		return $this->mpdf->Output();
	}


	/**
	 * Returns instance of Mpdf.
	 *
	 * @return Mpdf
	 */
	public function getMpdf()
	{
		return $this->mpdf;
	}



	/**
	 * Returns template.
	 *
	 * @return FileTemplate
	 */
	public function getTemplate()
	{
		return $this->template;
	}



	private function finalize()
	{
		if (!isset($this->template)) {
			throw new AlreadyRenderedException('This PDF document has been already rendered.');
		}
		ob_start();
		$this->template->render();
		$rendered = ob_get_clean();
		$this->mpdf->WriteHTML($rendered);
		unset($this->template);
	}

}
