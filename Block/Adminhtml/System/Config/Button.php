<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Button
 * @package Eadesigndev\PdfGeneratorPro\Block\Adminhtml\System\Config
 */
class Button extends Field
{
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('system/config/syncronize.phtml');
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getSynkUrl()
    {
        return $this->getUrl(
            'eadesign_pdf/*/syncronize'
        );
    }
}
