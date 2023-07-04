<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\TestsUnit\Service;

use Frosh\ThumbnailProcessor\Service\ConfigReader;
use Frosh\ThumbnailProcessor\Service\ThumbnailUrlTemplate;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;

class ThumbnailUrlTemplateTest extends TestCase
{
    /**
     * @dataProvider getSalesChannelIds
     */
    public function testGetUrl(?string $salesChannelId, string $mediaUrl, string $mediaPath, string $width): void
    {
        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::once())
            ->method('getConfig')->willReturn('{mediaUrl}/{mediaPath}?width={width}&uff');

        $class = new ThumbnailUrlTemplate($configReader);

        $url = $class->getUrl($mediaUrl, $mediaPath, $width);

        static::assertSame(\sprintf('%s/%s?width=%s&uff', $mediaUrl, $mediaPath, $width), $url);
    }

    /**
     * @dataProvider getSalesChannelIds
     */
    public function testGetUrlWithoutSetConfig(?string $salesChannelId, string $mediaUrl, string $mediaPath, string $width): void
    {
        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::once())
            ->method('getConfig')->willReturn('{mediaUrl}/{mediaPath}?width={width}');

        $class = new ThumbnailUrlTemplate($configReader);

        $url = $class->getUrl($mediaUrl, $mediaPath, $width);

        static::assertSame(\sprintf('%s/%s?width=%s', $mediaUrl, $mediaPath, $width), $url);
    }

    /**
     * @dataProvider getSalesChannelIds
     */
    public function testGetUrlGetPatternOnce(?string $salesChannelId, string $mediaUrl, string $mediaPath, string $width): void
    {
        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::once())
            ->method('getConfig')->willReturn('{mediaUrl}/{mediaPath}?width={width}');

        $class = new ThumbnailUrlTemplate($configReader);

        $class->getUrl($mediaUrl, $mediaPath, $width);

        $url = $class->getUrl($mediaUrl, $mediaPath, $width);

        static::assertSame(\sprintf('%s/%s?width=%s', $mediaUrl, $mediaPath, $width), $url);
    }

    /**
     * @return iterable<array{string|null, string, string, string}>
     */
    public static function getSalesChannelIds(): iterable
    {
        yield [null, 'https://www.anywebpage.test', 'media/78/a1/myimage.jpg', '200'];
        yield [Uuid::randomHex(), 'https://www.anyotherwebpage.test', 'media/aa/a1/myimage.jpg', '300'];
        yield [Uuid::randomHex(), 'https://www.anyother2webpage.test', 'media/aa/bb/myimage.jpg', '700'];
        yield [Uuid::randomHex(), 'https://www.anyother3webpage.test', 'media/aa/cc/myimage.jpg', '900'];
        yield [Uuid::randomHex(), 'https://www.anyother4webpage.test', 'media/aa/dd/myimage.jpg', '1000'];
    }
}
