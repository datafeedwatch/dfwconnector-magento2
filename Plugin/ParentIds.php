<?php
/**
 * Created by Qoliber
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Maciej Buchert <maciej@qsolutionsstudio.com>
 * @author      Wojciech M. Wnuk <wwnuk@qoliber.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager;

/**
 * Class ParentIds
 * @package DataFeedWatch\Connector\Plugin
 */
class ParentIds extends ExtensionAttributeAbstract
{
    public const ENTITY_TABLE = 'catalog_product_entity';
    public const RELATIONS_TABLE = 'catalog_product_relation';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param  \Magento\Catalog\Api\Data\ProductExtensionFactory $extensionFactory
     * @param  \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param  \Magento\Framework\Module\Manager         $moduleManager
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
     * @return Product
     */
    protected function setExtensionAttribute(Product $product): Product
    {
        if ($product->getTypeId() == Type::TYPE_SIMPLE) {
            return parent::setExtensionAttribute($product);
        }
        return $product;
    }

    /**
     * @param Product $product
     * @return array|mixed
     */
    protected function getExtensionData(Product $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::RELATIONS_TABLE);
        $entityTable = $this->resourceConnection->getTableName(self::ENTITY_TABLE);

        if (!$this->moduleManager->isEnabled('Magento_Staging')) {
            $query = $this->resourceConnection
                ->getConnection()
                ->select()
                ->from($tableName, 'parent_id')
                ->where(sprintf('child_id = %s', $product->getId()));

            return $connection->fetchCol($query);
        } else {
            $query = $connection->select()
                ->from(['r' => $tableName], '')
                ->join(
                    ['e' => $entityTable],
                    'r.parent_id = e.row_id',
                    ['parent_id' => 'e.entity_id']
                )
                ->where('r.child_id = ?', $product->getId());

            return $connection->fetchCol($query);
        }
    }

    /**
     * @return string
     */
    protected function getDataVar(): string
    {
        return 'ParentIds';
    }
}
