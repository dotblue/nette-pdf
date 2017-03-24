<?php

global $__dotblueNettePdfFonts;

foreach ($__dotblueNettePdfFonts as $font => $details) {
	$this->fontdata[$font] = $details;
}
