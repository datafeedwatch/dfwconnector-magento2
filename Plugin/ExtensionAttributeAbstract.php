<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\App\ResourceConnection;

/**
 * Class ExtensionAttributeAbstract
 * @package DataFeedWatch\Connector\Plugin
 */
abstract class ExtensionAttributeAbstract
{
    /**
     * @var ProductExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * ParentIds constructor.
     * @param ProductExtensionFactory $extensionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductExtensionFactory $extensionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param ProductRepository $subject
     * @param Product $product
     * @return Product
     */
    public function afterGet(
        ProductRepository $subject,
        Product $product
    ): Product {
        return $this->setExtensionAttribute($product);
    }

    /**
     * @param ProductRepository $subject
     * @param SearchResults $searchResults
     * @return SearchResults
     */
    public function afterGetList(
        ProductRepository $subject,
        SearchResults $searchResults
    ): SearchResults {
        $products = $searchResults->getItems();

        /** @var Product $product */
        foreach ($products as $product) {
            $this->setExtensionAttribute($product);
        }
        return $searchResults;
    }

    protected function setExtensionAttribute(Product $product): Product
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ?? $this->extensionFactory->create();
        $extensionAttributes->{'set' . $this->getDataVar()}($this->getExtensionData($product));
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    /**
     * @param Product $product
     * @return mixed
     */
    abstract protected function getExtensionData(Product $product);

    /**
     * @return string
     */
    abstract protected function getDataVar(): string;
}