<?php
/**
 * Copyright © EAdesign by Eco Active S.R.L.,All rights reserved.
 * See LICENSE for license details.
 */

namespace Eadesigndev\PdfGeneratorPro\Model\Email;

use Eadesigndev\PdfGeneratorPro\Model\FactoryInterface;
use Magento\Framework\ObjectManagerInterface;

class VariablesLoaderFactory implements FactoryInterface
{
    private $objectManager = null;

    private $instanceName = null;

    //@codingStandardsIgnoreLine
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        if (class_exists(\Magento\Email\Model\Source\Variables::class)) {
            $this->instanceName = \Magento\Email\Model\Source\Variables::class;
        }

        if (class_exists(\Magento\Variable\Model\Source\Variables::class)) {
            $this->instanceName = \Magento\Variable\Model\Source\Variables::class;
        }
    }

    public function create(array $data = [])
    {
        //@codingStandardsIgnoreLine
        return $this->objectManager->create($this->instanceName, $data);
    }
}
