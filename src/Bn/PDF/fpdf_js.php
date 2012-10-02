<?php
require('DZ/PDF.php');

class PDF_Javascript extends File_PDF {

	var $javascript;
	var $_n_js;

	function IncludeJS($script) {
		$this->javascript=$script;
	}

	function _putjavascript() {
		$this->_newobj();
		$this->_n_js=$this->_n;
		$this->_out('<<');
		$this->_out('/Names [(EmbeddedJS) '.($this->_n+1).' 0 R ]');
		$this->_out('>>');
		$this->_out('endobj');
		$this->_newobj();
		$this->_out('<<');
		$this->_out('/S /JavaScript');
		$this->_out('/JS '.$this->_textstring($this->javascript));
		$this->_out('>>');
		$this->_out('endobj');
	}

	function _putresources() {
		parent::_putresources();
		if (!empty($this->javascript)) {
			$this->_putjavascript();
		}
	}

	function _putcatalog() {
		parent::_putcatalog();
		if (isset($this->javascript)) {
			$this->_out('/Names <</JavaScript '.($this->_n_js).' 0 R>>');
		}
	}
}
?>
