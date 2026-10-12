<?php declare(strict_types=1);

namespace Swag\ProductBadge\Tests\Integration\Subscriber;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRoute;
use Shopware\Core\Content\Test\Product\ProductBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Test\Stub\Framework\IdsCollection;
use Shopware\Core\Test\TestDefaults;
use Swag\ProductBadge\Entity\ProductBadgeEntity;
use Swag\ProductBadge\Extension\ProductExtension;
use Swag\ProductBadge\Subscriber\ProductBadgeSubscriber;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[Package('inventory')]
class ProductListingRouteTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use KernelTestBehaviour;

    private IdsCollection $ids;

    private AbstractProductListingRoute $route;

    private SalesChannelContext $context;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();
        $this->route = static::getContainer()->get(ProductListingRoute::class);
        $this->createProducts();

        $this->context = static::getContainer()->get(SalesChannelContextFactory::class)
            ->create(Uuid::randomHex(), TestDefaults::SALES_CHANNEL);
    }

    public function testListingWorksWithoutFilter(): void
    {
        $listing = $this->route
            ->load($this->ids->get('category'), new Request(), $this->context, new Criteria())
            ->getResult();

        static::assertCount(3, $listing->getEntities());
    }

    public function testListingFiltersReally(): void
    {
        $request = new Request();
        $request->query->set('swag-product-badge', $this->ids->get('sale'));

        $listing = $this->route
            ->load($this->ids->get('category'), $request, $this->context, new Criteria())
            ->getResult();

        static::assertCount(2, $listing->getEntities());
    }

    private function createProducts(): void
    {
        /** @var EntityRepository<ProductCollection> $productRepository */
        $productRepository = static::getContainer()->get('product.repository');

        $productRepository->create([
            $this->product('with-both', ['sale', 'new']),
            $this->product('with-sale', ['sale']),
            $this->product('plain', []),
        ], Context::createCLIContext());
    }

    /**
     * @param list<string> $badgeKeys
     *
     * @return array<string, mixed>
     */
    private function product(string $key, array $badgeKeys): array
    {
        $product = (new ProductBuilder($this->ids, $key))
            ->price(10)
            ->visibility()
            ->category('category')
            ->build();

        $product[ProductExtension::EXTENSION_NAME] = array_map(
            fn (string $badgeKey) => ['id' => $this->ids->get($badgeKey), 'name' => $badgeKey, 'color' => '#fff', 'position' => 1],
            $badgeKeys
        );

        return $product;
    }
}
