<?php

namespace DotBlue\Mpdf\DI;

use Nette\DI;
use ReflectionClass;


class Extension extends DI\CompilerExtension
{

	/** @var array */
	private $defaults = [
		'fonts' => [],
		'themes' => [],
	];



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$templatesDir = $config['templatesDir'];
		unset($config['templatesDir']);

		$themes = $config['themes'];
		unset($config['themes']);

		$fonts = $this->configureFonts($config['fonts']);
		unset($config['fonts']);

		if (!$container->getByType('DotBlue\Mpdf\ITemplateFactory')) {
			$container->addDefinition($this->prefix('templateFactory'))
				->setClass('DotBlue\Mpdf\TemplateFactories\DefaultLatteTemplateFactory');
		}

		$factory = $container->addDefinition($this->prefix('factory'))
			->setClass('DotBlue\Mpdf\DocumentFactory', [
				$templatesDir,
				$config,
				$fonts
			]);

		foreach ($themes as $name => $setup) {
			$factory->addSetup('addTheme', [
				$name,
				$setup,
			]);
		}
	}



	private function configureFonts(array $fonts)
	{
		if (!$fonts) {
			return [];
		}

		$reflection = new ReflectionClass('Mpdf\Mpdf');
		$fontsDir = substr($reflection->getFileName(), 0, -8) . 'ttfonts';

		foreach ($fonts as $font => $details) {
			foreach (['R', 'B', 'I', 'BI'] as $type) {
				if (isset($details[$type])) {
					$fonts[$font][$type] = $this->getRelativePath(
						$fontsDir,
						$details[$type]
					);
				}
			}
		}

		return $fonts;
	}



	/**
	 * @see http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
	 */
	private function getRelativePath($from, $to)
	{
		// some compatibility fixes for Windows paths
		$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
		$to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
		$from = str_replace('\\', '/', $from);
		$to   = str_replace('\\', '/', $to);

		$from     = explode('/', $from);
		$to       = explode('/', $to);
		$relPath  = $to;

		foreach($from as $depth => $dir) {
			// find first non-matching dir
			if ($dir === $to[$depth]) {
				// ignore this directory
				array_shift($relPath);
			} else {
				// get number of remaining dirs to $from
				$remaining = count($from) - $depth;
				if ($remaining > 1) {
					// add traversals up to first matching dir
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}

		return implode('/', $relPath);
	}

}
