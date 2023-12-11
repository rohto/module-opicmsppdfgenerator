<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product as ProductObject;
use Magento\Framework\View\LayoutInterface;
use Eadesigndev\PdfGeneratorPro\Block\Product\View\Attributes;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

class Product implements CustomInterface
{

    /**
     * @var ProductObject
     */
    private $source;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var GalleryReadHandler
     */
    private $galleryReadHandler;

    /**
     * Product constructor.
     * @param ImageHelper $imageHelper
     * @param LayoutInterface $layout
     * @param GalleryReadHandler $galleryReadHandler
     */
    public function __construct(
        ImageHelper $imageHelper,
        LayoutInterface $layout,
        GalleryReadHandler $galleryReadHandler
    ) {
        $this->imageHelper = $imageHelper;
        $this->layout = $layout;
        $this->galleryReadHandler = $galleryReadHandler;
    }

    /**
     * @param $source
     * @return $this
     */
    public function entity($source)
    {
        $this->source = $source;
        $this->imageProcessor();

        return $this;
    }

    /**
     * @return ProductObject
     */
    public function processAndReadVariables()
    {
        return $this->source;
    }

    /**
     * Add the images, pare the image gallery to get all
     */
    private function imageProcessor()
    {
        $this->mediaFiles();
        $this->image();
        $this->smallImage();
        $this->thumbnail();
        $this->attributeTableSource();
    }

    /**
     * @return $this
     */
    private function mediaFiles()
    {
        $this->galleryReadHandler->execute($this->source);
        $media = $this->source->getMediaGalleryImages();

        if (empty($media)) {
            return $this;
        }

        $i = 0;
        foreach ($media as $mediaImage) {
            $t = $i++;
            $key = "custom_image_{$t}";
            $this->source->setData($key, $this->imageHtml($mediaImage['url']));
        }

        return $this;
    }

    /**
     * @return ProductObject
     */
    private function image()
    {
        $html = $this->imageHtml($this->imageUrl('image', 300));
        $this->source->setImageHtml($html);
        return $this->source;
    }

    /**
     * @return ProductObject
     */
    private function smallImage()
    {
        $html = $this->imageHtml($this->imageUrl('small_image', 100));
        $this->source->setSmallImageHtml($html);
        return $this->source;
    }

    /**
     * @return ProductObject
     */
    private function thumbnail()
    {
        $html = $this->imageHtml($this->imageUrl('thumbnail', 50));
        $this->source->setThumbnailImageHtml($html);
        return $this->source;
    }

    /**
     * @param $type
     * @param $size
     * @return string
     */
    private function imageUrl($type, $size)
    {
        $imageUrl = $this->imageHelper
            ->init($this->source, $type)
            ->setImageFile($this->source->getImage())
            ->resize($size)
            ->getUrl();

        return $imageUrl;
    }

    /**
     * @param $imageUrl
     * @return string
     */
    public function imageHtml($imageUrl)
    {
        $html = '<img src="' . $imageUrl . '"/>';
        return $html;
    }

    private function attributeTableSource()
    {
        $html = $this->layout
            ->createBlock(Attributes::class)
            ->setProduct($this->source)
            ->toHtml();

        $this->source->setAttributesTableHtml($html);
        return $this->source;
    }
}
