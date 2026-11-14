<?php

namespace DataFeedWatch\Connector\Test\Unit\Plugin;

use DataFeedWatch\Connector\Model\QtyAndStockFactory;
use DataFeedWatch\Connector\Plugin\QtyAndStock;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Helper\Minsaleqty;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class QtyAndStockTest extends TestCase
{
    /**
     * @var AdapterInterface|Stub
     */
    private $connection;

    /**
     * @var ScopeConfigInterface|Stub
     */
    private $scopeConfig;

    /**
     * @var Minsaleqty|Stub
     */
    private $minSaleQty;

    /**
     * @var QtyAndStock
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->connection = $this->createStub(AdapterInterface::class);
        $resourceConnection = $this->createStub(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($this->connection);
        $resourceConnection->method('getTableName')->willReturnArgument(0);
        $this->scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $this->minSaleQty = $this->createStub(Minsaleqty::class);

        $this->plugin = new QtyAndStock(
            $this->createStub(ProductExtensionFactory::class),
            $resourceConnection,
            $this->createStub(Manager::class),
            $this->createStub(StoreManagerInterface::class),
            $this->scopeConfig,
            $this->createStub(State::class),
            $this->createStub(RequestInterface::class),
            $this->createStub(QtyAndStockFactory::class),
            $this->createStub(DataObjectHelper::class),
            $this->minSaleQty
        );
    }

    public function testManageStockFallsBackToConfigWhenStockItemRowIsMissing(): void
    {
        $this->connection->method('fetchRow')->willReturn(false);
        $this->scopeConfig->method('getValue')->willReturn('1');

        $this->assertTrue($this->invoke('getManageStock'));
    }

    public function testManageStockUsesRowValueWhenConfigIsNotUsed(): void
    {
        $this->connection->method('fetchRow')
            ->willReturn(['manage_stock' => '0', 'use_config_manage_stock' => '0']);

        $this->assertFalse($this->invoke('getManageStock'));
    }

    public function testMinSaleQtyFallsBackToConfigWhenStockItemRowIsMissing(): void
    {
        $this->connection->method('fetchRow')->willReturn(false);
        $this->minSaleQty->method('getConfigValue')->willReturn(2.0);

        $this->assertSame(2.0, $this->invoke('getMinSaleQty'));
    }

    public function testMinSaleQtyUsesRowValueWhenConfigIsNotUsed(): void
    {
        $this->connection->method('fetchRow')
            ->willReturn(['min_sale_qty' => '3.0000', 'use_config_min_sale_qty' => '0']);

        $this->assertSame(3.0, $this->invoke('getMinSaleQty'));
    }

    /**
     * @return mixed
     */
    private function invoke(string $method)
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn(10);
        $product->method('__call')->willReturn(null);

        $reflection = new \ReflectionMethod($this->plugin, $method);

        return $reflection->invoke($this->plugin, $product);
    }
}
