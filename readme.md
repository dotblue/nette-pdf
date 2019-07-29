#### Requirements

- PHP 7.3+
- [mpdf/mpdf](https://github.com/finwe/mpdf) ~7.0
- [nette/application](https://github.com/nette/application) >= 3.0
- [nette/di](https://github.com/nette/di) >= 3.0
- [nette/utils](https://github.com/nette/utils) >= 3.0

## Installation

1) Copy source using [Composer](http://getcomposer.org/):
```sh
$ composer require dotblue/nette-pdf@~3.0
```

2) Register as Configurator's extension:
```
extensions:
	mpdf: DotBlue\Mpdf\DI\Extension
```

## Configuration

First you need to tell the addon where you store PDF documents' templates.

```
mpdf:
	templatesDir: %appDir%/templates/pdf
```

In the app, you usually have several types of PDF documents that you wish to generate. Such type is called *theme*. Each theme should have its own directory located in `templatesDir`. You can configure theme via many directives which mPDF supports.

```
mpdf:
	themes:
		invoice:
			margin:
				left: 20
				right: 20
				top: 20
				bottom: 20
```

Default settings are following:

```
encoding: utf-8
img_dpi: 120
format: A4
margin:
	left: 0
	right: 0
	top: 0
	bottom: 0
```

Each theme has built-in support for external stylesheet. If you put `style.css` file into theme's directory, it will be automatically bundled into PDF document.

### Fonts

Custom fonts can be configured in following way:

```
fonts:
	roboto:
		R: %appDir%/fonts/Roboto.ttf
```

Supported types are R (regular), B (bold), I (italic) and BI (bold & italic).

## Usage

There is only one service: `DotBlue\Mpdf\DocumentFactory`. Granted that you have `default.latte` file in our `invoice` theme directory, you can create new PDF document like this:

```php
$invoiceDocument = $documentFactory->createPdf('invoice');
```

Variable `$invoiceDocument` is instance of `DotBlue\Mpdf\Document`, which provides simple API for printing or saving, and for linking images. If you would like to save the invoice somewhere on hard drive, you can call `saveTo()` method.

```php
$invoiceDocument->saveTo(__DIR__ . '/invoice.pdf');
```

Or you can show document to user in browser:

```php
$invoiceDocument->printPdf();
```

### Variants

Theme can support more variants. Actually method `createPdf()` has second optional argument, and its default value is `default.latte`. By changing this, your theme can support many variants either of type or for example of localization.

As third argument you can pass array of directives right for mPDF, which will override your theme default settings.
