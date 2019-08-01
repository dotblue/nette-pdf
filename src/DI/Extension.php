<?php declare(strict_types=1);

namespace DotBlue\Mpdf\DI;

use DotBlue;
use Mpdf;
use Nette;


class Extension extends Nette\DI\CompilerExtension
{

	/** @var array */
	private $defaults = [
		'defaults' => [],
		'fonts' => [],
		'fontsDirs' => [],
		'tempDir' => NULL,
		'templatesDir' => NULL,
		'themes' => [],
	];



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$factory = $container->addDefinition($this->prefix('factory'))
			->setFactory(DotBlue\Mpdf\DocumentFactory::class, [
				$config['tempDir'],
				$config['templatesDir'],
				$config['defaults'],
				[
					'fonts' => count($config['fonts']) > 0 ? $config['fonts'] : NULL,
					'fontsDirs' => count($config['fontsDirs']) > 0 ? $config['fontsDirs'] : NULL,
				]
			]);

		foreach ($config['themes'] as $name => $setup) {
			$factory->addSetup('addTheme', [
				$name,
				$setup,
			]);
		}
	}

}
