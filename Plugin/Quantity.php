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

class Quantity extends ExtensionAttributeAbstract
{
    const STOCK_TABLE = "inventory_source_item";
    const LEGACY_STOCK_TABLE = "cataloginventory_stock_item";
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
        parent::__construct($extensionFactory, $resourceConnection);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param Product $product
     * @return float
     */
    protected function getExtensionData(Product $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::STOCK_TABLE);

        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $query = sprintf("SELECT SUM(`quantity`) FROM `%s` WHERE `sku` = :sku AND `status` = 1", $tableName);
            $bind = ['sku' => $product->getSku()];
        } else {
            $tableName = $this->resourceConnection->getTableName(self::LEGACY_STOCK_TABLE);

            $query = sprintf(
                "SELECT SUM(`qty`) FROM `%s` WHERE `product_id` = :product_id AND `is_in_stock` = 1%s",
                $tableName, $product->getWebsiteId() ? " AND `website_id` = :website_id" : ''
            );
            $bind = ['product_id' => $product->getId()];
            if ($product->getWebsiteId()) {
                $bind['website_id'] = $product->getWebsiteId();
            }
        }

        return $connection->fetchOne($query, $bind) ?: 0;
    }

    protected function getDataVar(): string
    {
        return 'Quantity';
    }
}
