<?php
/**
 * Created by Q-Solutions Studio
 * Date: 30.09.2019
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager;

class Quantity
{
    const STOCK_TABLE = "inventory_source_item";
    const LEGACY_STOCK_TABLE = "cataloginventory_stock_item";

    /**
     * @var ProductExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * ParentIds constructor.
     * @param ProductExtensionFactory $extensionFactory
     * @param ResourceConnection $resourceConnection
     * @param Manager $moduleManager
     */
    public function __construct(
        ProductExtensionFactory $extensionFactory,
        ResourceConnection $resourceConnection,
        Manager $moduleManager
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param ProductRepository $subject
     * @param Product $product
     * @return Product
     */
    public function afterGet(
        ProductRepository $subject,
        Product $product
    ) {
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
    ) {
        $products = $searchResults->getItems();

        /** @var Product $product */
        foreach ($products as $product) {
            $this->setExtensionAttribute($product);
        }
        return $searchResults;
    }

    /**
     * @param Product $product
     * @return Product
     */
    protected function setExtensionAttribute(Product $product)
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ?? $this->extensionFactory->create();
        $extensionAttributes->setQuantity(
            $this->getQuantity($product)
        );
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    /**
     * @param Product $product
     * @return float
     */
    protected function getQuantity(Product $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::STOCK_TABLE);

        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $query = sprintf("SELECT SUM(`quantity`) FROM `%s` WHERE `sku` = '%s' AND `status` = 1", $tableName, $product->getSku());
        } else {
            $tableName = $this->resourceConnection->getTableName(self::LEGACY_STOCK_TABLE);

            $query = sprintf(
                "SELECT SUM(`qty`) FROM `%s` WHERE `product_id` = '%s' AND `is_in_stock` = 1%s",
                $tableName, $product->getId(), $product->getWebsiteId() ? sprintf(" AND `website_id` = %s", $product->getWebsiteId()) : ''
            );
        }

        return $connection->fetchOne($query) ?: 0;
    }
}
