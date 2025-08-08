<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Service;

use Frosh\ThumbnailProcessor\Service\SalesChannelIdDetector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\ProductExport\ProductExportCollection;
use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Shopware\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesChannelIdDetectorTest extends TestCase
{
    public function testGetSalesChannelIdFromMainRequest(): void
    {
        $requestStack = new RequestStack();

        $mainRequest = new Request(
            attributes: [
                PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID => 'mySalesChannelId',
            ],
        );

        $requestStack->push($mainRequest);
        $requestStack->push(new Request(['foo' => 'bar']));

        $productExportRepository = $this->createMock(EntityRepository::class);
        $class = new SalesChannelIdDetector($requestStack, $productExportRepository);

        static::assertSame('mySalesChannelId', $class->getSalesChannelId());
    }

    public function testGetSalesChannelIdWithoutMainRequest(): void
    {
        $requestStack = new RequestStack();

        $productExportRepository = $this->createMock(EntityRepository::class);
        $class = new SalesChannelIdDetector($requestStack, $productExportRepository);

        static::assertNull($class->getSalesChannelId());
    }

    public function testGetSalesChannelIdNull(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $productExportRepository = $this->createMock(EntityRepository::class);
        $class = new SalesChannelIdDetector($requestStack, $productExportRepository);

        static::assertNull($class->getSalesChannelId());
    }

    public function testGetSalesChannelId(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(
            attributes: [
                PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID => 'mySalesChannelId',
            ],
        ));

        $productExportRepository = $this->createMock(EntityRepository::class);
        $class = new SalesChannelIdDetector($requestStack, $productExportRepository);

        static::assertSame('mySalesChannelId', $class->getSalesChannelId());
    }

    public function testGetSalesChannelIdProductExport(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(
            attributes: [
                '_route' => 'store-api.product.export',
                'fileName' => 'anyFilename',
                'accessKey' => 'anyAccessKey',
            ],
        ));

        $productExportEntity = new ProductExportEntity();
        $productExportEntity->setId(Uuid::randomHex());
        $productExportEntity->setFileName('anyFilename');
        $productExportEntity->setAccessKey('anyAccessKey');
        $productExportEntity->setSalesChannelId('myCoolSalesChannelId');

        $productExportCollection = new ProductExportCollection();
        $productExportCollection->add($productExportEntity);

        /** @var StaticEntityRepository<ProductExportCollection> */
        $productExportRepository = new StaticEntityRepository([$productExportCollection]);

        $class = new SalesChannelIdDetector($requestStack, $productExportRepository);

        static::assertSame('myCoolSalesChannelId', $class->getSalesChannelId());
    }

    public function testGetSalesChannelIdProductExportWithoutFileNameAndAccessKey(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(
            attributes: [
                '_route' => 'store-api.product.export',
            ],
        ));

        /** @var StaticEntityRepository<ProductExportCollection>&MockObject */
        $productExportRepository = $this->createMock(StaticEntityRepository::class);
        $productExportRepository->expects(static::never())->method('search');

        $class = new SalesChannelIdDetector($requestStack, $productExportRepository);

        static::assertNull($class->getSalesChannelId());
    }
}
