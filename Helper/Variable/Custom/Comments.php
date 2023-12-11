<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Sales\Block\Order\Creditmemo;
use Magento\Sales\Model\Order;

class Comments
{
    /**
     * @var Order|Order\Invoice|Creditmemo
     */
    private $source;

    /**
     * @param $source
     * @return $this
     */
    public function entity($source)
    {
        if (is_object($source)) {
            $this->source = $source;
            $this->addComments();
            return $this;
        }
    }

    /**
     * @return $this
     */
    public function addComments()
    {
        if ($this->source instanceof ProductModel) {
            return $this;
        }
        $commentsCollection =  $this->source->getCommentsCollection();

        $commentString = '';

        if (!empty($commentsCollection) && is_object($commentsCollection)) {
            foreach ($commentsCollection->getItems() as $comment) {
                $commentString .= $comment->getData('comment') . '<br>';
            }
        }

        $this->source->setData('comments', $commentString);

        return $this;
    }
}
