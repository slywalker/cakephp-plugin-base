<?php
App::uses('CakeEmail', 'Network/Email');

use \InlineStyle\InlineStyle;

class HtmlEmail extends CakeEmail {

	protected $_stylePath = null;

	public function stylePath($path = null) {
		if ($path === null) {
			return $this->_stylePath;
		}
		$this->_stylePath = $path;
		return $this;
	}

	public function reset() {
		parent::reset();

		$this->_stylePath = null;
		return $this;
	}

	protected function _render($content) {
		$this->viewVars(array('charset' => $this->charset));
		return parent::_render($content);
	}

	protected function _renderTemplates($content) {
		$rendered = parent::_renderTemplates($content);

		$style = false;
		if (!empty($this->_stylePath) && file_exists($this->_stylePath)) {
			$style = file_get_contents($this->_stylePath);
		}

		array_walk($rendered, function(&$val, $key) use ($style) {
			if ($key === 'html' && $style) {
				$regex = '/(<meta.*?charset=)(.*?)(["\']{1}.*?>)/i';

				$val = mb_convert_encoding($val, $this->_appCharset, $this->charset);
				$val = preg_replace($regex, '$1' . $this->_appCharset . '$3', $val);

				libxml_use_internal_errors(true);
				$html = new InlineStyle($val);
				libxml_clear_errors();
				$html->applyStylesheet($style);
				$val = $html->getHTML();

				$val = mb_convert_encoding($val, $this->charset, $this->_appCharset);
				$val = preg_replace($regex, '$1' . $this->charset . '$3', $val);
			}
		});

		return $rendered;
	}

}