<?php

namespace Eadesigndev\PdfGeneratorPro\Test\Unit\Model;

use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository;
use Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator as PdfgeneratorResourceModel;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;
use Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator as PdfgeneratorModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Message\ManagerInterface;

/**
 * Test for \Pdfgenerator\Model\PdfgeneratorRepository
 * Class PdfgeneratorRepositoryTest
 * @package Eadesigndev\PdfGeneratorPro\Test\Integration
 */
class PdfgeneratorRepositoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var /Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
     */
    public $objectManager;

    /**
     * @var PdfgeneratorRepository
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator
     */
    private $pdfCustomizerResourceModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory
     */
    private $pdfCustomizerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Eadesigndev\PdfGeneratorPro\Api\Data\TemplatesInterface;
     */
    private $pdfGenerator;

    public function setUp()
    {

        $this->objectManager = new ObjectManager($this);

        $this->pdfCustomizerResourceModel = $this->getMockBuilder(PdfgeneratorResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pdfCustomizerFactory = $this->getMockBuilder(PdfgeneratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        /** @var PdfgeneratorModel pdfGenerator */
        $this->pdfGenerator = $this->objectManager->getObject(PdfgeneratorModel::class);

        $this->pdfCustomizerFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->pdfGenerator);

        $messageManager = $this->getMockBuilder(ManagerInterface::class)->getMock();

        $this->repository = new PdfgeneratorRepository(
            $this->pdfCustomizerResourceModel,
            $this->pdfGenerator,
            $this->pdfCustomizerFactory,
            $messageManager
        );
    }

    public function testSave()
    {
        $this->pdfCustomizerResourceModel
            ->expects($this->once())
            ->method('save')
            ->with($this->pdfGenerator)
            ->willReturnSelf();

        $this->assertEquals($this->pdfGenerator, $this->repository->save($this->pdfGenerator));
    }

    public function testGetById()
    {
        $id = 1;
        $this->pdfCustomizerResourceModel
            ->expects($this->once())
            ->method('load')
            ->with($this->pdfGenerator->setEntityId($id))
            ->willReturnSelf();

        $this->assertEquals($this->pdfGenerator, $this->repository->getById($id));
    }

    public function testDelete()
    {

        $this->pdfCustomizerResourceModel
            ->expects($this->once())
            ->method('delete')
            ->with($this->pdfGenerator)
            ->willReturnSelf();

        $this->assertTrue($this->repository->delete($this->pdfGenerator));
    }

    public function testDeleteById()
    {
        $id = 1;

        $this->pdfCustomizerResourceModel
            ->expects($this->once())
            ->method('load')
            ->with($this->pdfGenerator->setEntityId($id))
            ->willReturnSelf();

        $this->assertTrue($this->repository->deleteById($id));
    }
}
