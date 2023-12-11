<?php

namespace Eadesigndev\PdfGeneratorPro\Model;

use Magento\Framework\ObjectManagerInterface;

class PdfgeneratorFactory implements FactoryInterface
{

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * PdfgeneratorFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = Pdfgenerator::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data = [])
    {
        //@codingStandardsIgnoreLine
        return $this->objectManager->create($this->instanceName, $data);
    }
}
