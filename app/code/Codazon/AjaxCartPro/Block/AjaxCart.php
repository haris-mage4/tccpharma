<?php
/**
 * Copyright © 2016 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxCartPro\Block;
class AjaxCart extends \Magento\Framework\View\Element\Template
{
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\View\DesignInterface $design,
		array $data = []
	){	
		$this->_design = $design;
		parent::__construct($context, $data);
	}
	
	public function _toHtml(){
		$themePath = $this->_design->getDesignTheme()->getFullPath();
		if (strpos($themePath, 'Codazon') !== false) {
			return parent::_toHtml();
		}else{
			return '';
		}
	}
	
}