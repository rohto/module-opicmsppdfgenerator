<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Pdfgenerator\Edit;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DuplicateButton
 */
class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->_isAllowedAction(Templates::ADMIN_RESOURCE_SAVE)) {
            $data = [];
            if ($this->getTemplateId()) {
                $data = [
                    'label' => __('Duplicate Template'),
                    'class' => 'delete',
                    'on_click' => sprintf("location.href = '%s';", $this->getDuplicateUrl()),
                    'sort_order' => 20,
                ];
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl(
            '*/*/duplicate',
            ['template_id' => $this->getTemplateId()]
        );
    }
}
