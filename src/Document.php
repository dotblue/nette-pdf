<?php declare(strict_types=1);

namespace DotBlue\Mpdf;

use Mpdf;
use Nette;


class Document
{

	use Nette\SmartObject;

	/** @var Mpdf\Mpdf */
	private $mpdf;

	/** @var Nette\Application\UI\ITemplate */
	private $template;



	public function __construct(
		Mpdf\Mpdf $mpdf,
		Nette\Application\UI\ITemplate $template
	)
	{
		$this->mpdf = $mpdf;
		$this->template = $template;
	}



	public function addImageFromPath(string $name, string $path): self
	{
		$this->addImage($name, Nette\Utils\Image::fromFile($path));
		return $this;
	}



	public function addImage(string $name, Nette\Utils\Image $image): elf
	{
		$this->mpdf->$name = $image->toString(Nette\Utils\Image::PNG);
		return $this;
	}



	public function saveTo(string $path): void
	{
		$this->finalize();
		$this->mpdf->Output($path, Mpdf\Output\Destination::FILE);
	}



	public function render(): string
	{
		$this->finalize();
		return $this->mpdf->Output('', Mpdf\Output\Destination::STRING_RETURN);
	}



	public function forceDownload(string $filename): void
	{
		$this->finalize();
		$this->mpdf->Output($filename, Mpdf\Output\Destination::DOWNLOAD);
	}



	public function printPdf(): void
	{
		$this->finalize();
		$this->mpdf->Output('', Mpdf\Output\Destination::INLINE);
	}



	public function getMpdf(): Mpdf\Mpdf
	{
		return $this->mpdf;
	}



	public function getTemplate(): Nette\Application\UI\ITemplate
	{
		return $this->template;
	}



	private function finalize(): void
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
