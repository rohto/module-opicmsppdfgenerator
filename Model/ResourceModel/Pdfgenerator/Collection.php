<?php

namespace Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    //@codingStandardsIgnoreLine
    protected $_idFieldName = 'template_id';

    /**
     * Init resource model
     * @return void
     */
    public function _construct()
    {

        $this->_init(
            'Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator',
            'Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator'
        );

        $this->_map['fields']['template_id'] = 'main_table.template_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }

        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function _afterLoad()
    {
        $this->performAfterLoad('xtea_pdf_store', 'template_id');

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    public function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('xtea_pdf_store', 'template_id');
    }
}
