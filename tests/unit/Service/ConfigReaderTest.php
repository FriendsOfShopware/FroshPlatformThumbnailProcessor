<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Service;

use Frosh\ThumbnailProcessor\Controller\Api\TestController;
use Frosh\ThumbnailProcessor\Service\ConfigReader;
use Frosh\ThumbnailProcessor\Service\SalesChannelIdDetector;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Tests\Unit\Common\Stubs\SystemConfigService\StaticSystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ConfigReaderTest extends TestCase
{
    /**
     * @dataProvider getSalesChannelIds
     */
    public function testGetConfig(?string $salesChannelId): void
    {
        $systemConfigService = new StaticSystemConfigService();
        $systemConfigService->set('FroshPlatformThumbnailProcessor.config.AnyString', 'test', $salesChannelId);
        $systemConfigService->set('FroshPlatformThumbnailProcessor.config.AnyNumber', 5, $salesChannelId);

        $salesChannelIdDetector = $this->createMock(SalesChannelIdDetector::class);
        $salesChannelIdDetector->expects(static::once())
            ->method('getSalesChannelId')->willReturn($salesChannelId);

        $class = new ConfigReader($systemConfigService, $salesChannelIdDetector, new RequestStack());

        $anyStringConfig = $class->getConfig('AnyString');
        static::assertIsString($anyStringConfig);
        static::assertSame('test', $anyStringConfig);

        $anyNumberConfig = $class->getConfig('AnyNumber');
        static::assertIsNumeric($anyNumberConfig);
        static::assertSame(5, $anyNumberConfig);
    }

    /**
     * @dataProvider getWidths
     */
    public function testGetConfigProcessOriginalImageMaxWidthAlwaysString(mixed $width): void
    {
        $systemConfigService = new StaticSystemConfigService();
        $systemConfigService->set('FroshPlatformThumbnailProcessor.config.ProcessOriginalImageMaxWidth', $width);

        $salesChannelIdDetector = $this->createMock(SalesChannelIdDetector::class);
        $salesChannelIdDetector->expects(static::once())
            ->method('getSalesChannelId')->willReturn(null);

        $class = new ConfigReader($systemConfigService, $salesChannelIdDetector, new RequestStack());

        $anyStringConfig = $class->getConfig('ProcessOriginalImageMaxWidth');
        static::assertIsString($anyStringConfig);
        static::assertSame('300', $anyStringConfig);
    }

    public function testGetConfigProcessOriginalImageMaxWidthFallbacksTo3000(): void
    {
        $systemConfigService = new StaticSystemConfigService();
        $systemConfigService->set('FroshPlatformThumbnailProcessor.config.ProcessOriginalImageMaxWidth', null);

        $salesChannelIdDetector = $this->createMock(SalesChannelIdDetector::class);
        $salesChannelIdDetector->expects(static::once())
            ->method('getSalesChannelId')->willReturn(null);

        $class = new ConfigReader($systemConfigService, $salesChannelIdDetector, new RequestStack());

        $anyStringConfig = $class->getConfig('ProcessOriginalImageMaxWidth');
        static::assertIsString($anyStringConfig);
        static::assertSame('3000', $anyStringConfig);
    }

    /**
     * @dataProvider getActiveValues
     */
    public function testGetConfigActive(mixed $value): void
    {
        $systemConfigService = new StaticSystemConfigService();
        $systemConfigService->set('FroshPlatformThumbnailProcessor.config.Active', $value);

        $salesChannelIdDetector = $this->createMock(SalesChannelIdDetector::class);
        $salesChannelIdDetector->expects(static::once())
            ->method('getSalesChannelId')->willReturn(null);

        $class = new ConfigReader($systemConfigService, $salesChannelIdDetector, new RequestStack());

        static::assertTrue($class->getConfig('Active'));
    }

    public function testGetConfigActiveWithActiveTest(): void
    {
        $systemConfigService = new StaticSystemConfigService();
        $systemConfigService->set('FroshPlatformThumbnailProcessor.config.Active', false);

        $salesChannelIdDetector = $this->createMock(SalesChannelIdDetector::class);
        $salesChannelIdDetector->expects(static::once())
            ->method('getSalesChannelId')->willReturn(null);

        $requestStack = new RequestStack();
        $requestStack->push(new Request(attributes: [TestController::REQUEST_ATTRIBUTE_TEST_ACTIVE => true]));

        $class = new ConfigReader($systemConfigService, $salesChannelIdDetector, $requestStack);

        static::assertTrue($class->getConfig('Active'));
    }

    /**
     * @return iterable<array{null|string}>
     */
    public static function getSalesChannelIds(): iterable
    {
        yield [null];
        yield [Uuid::randomHex()];
        yield [Uuid::randomHex()];
        yield [Uuid::randomHex()];
        yield [Uuid::randomHex()];
    }

    /**
     * @return iterable<array{int|string|float}>
     */
    public static function getWidths(): iterable
    {
        yield [300];
        yield ['300'];
        yield [300.00];
    }

    /**
     * @return iterable<array{null|bool}>
     */
    public function getActiveValues(): iterable
    {
        yield [null];
        yield [true];
    }
}
