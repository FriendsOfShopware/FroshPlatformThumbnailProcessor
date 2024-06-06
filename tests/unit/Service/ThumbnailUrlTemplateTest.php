<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Service;

use Frosh\ThumbnailProcessor\Service\ConfigReader;
use Frosh\ThumbnailProcessor\Service\ThumbnailUrlTemplate;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;

class ThumbnailUrlTemplateTest extends TestCase
{
	/**
     * @dataProvider getSalesChannelIds
     */
    public function testGetUrl(?string $salesChannelId, string $mediaUrl, string $mediaPath, string $width, ?\DateTimeInterface $date): void
    {
        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::once())
            ->method('getConfig')->willReturn('{mediaUrl}/{mediaPath}?width={width}&updatedAt={mediaUpdatedAt}&uff');

        $class = new ThumbnailUrlTemplate($configReader);

        $url = $class->getUrl($mediaUrl, $mediaPath, $width, $date);

        static::assertSame(\sprintf('%s/%s?width=%s&updatedAt=%s&uff', $mediaUrl, $mediaPath, $width, $date?->getTimestamp() ?: '0'), $url);
    }

    /**
     * @dataProvider getSalesChannelIds
     */
    public function testGetUrlWithoutSetConfig(?string $salesChannelId, string $mediaUrl, string $mediaPath, string $width, ?\DateTimeInterface $date): void
    {
        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::once())
            ->method('getConfig')->willReturn('{mediaUrl}/{mediaPath}?width={width}&updatedAt={mediaUpdatedAt}');

        $class = new ThumbnailUrlTemplate($configReader);

        $url = $class->getUrl($mediaUrl, $mediaPath, $width, $date);

        static::assertSame(\sprintf('%s/%s?width=%s&updatedAt=%s', $mediaUrl, $mediaPath, $width, $date?->getTimestamp() ?: '0'), $url);
    }

    /**
     * @dataProvider getSalesChannelIds
     */
    public function testGetUrlGetPatternOnce(?string $salesChannelId, string $mediaUrl, string $mediaPath, string $width, ?\DateTimeInterface $date): void
    {
        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::once())
            ->method('getConfig')->willReturn('{mediaUrl}/{mediaPath}?width={width}&updatedAt={mediaUpdatedAt}');

        $class = new ThumbnailUrlTemplate($configReader);

        $class->getUrl($mediaUrl, $mediaPath, $width, $date);

        $url = $class->getUrl($mediaUrl, $mediaPath, $width, $date);

        static::assertSame(\sprintf('%s/%s?width=%s&updatedAt=%s', $mediaUrl, $mediaPath, $width, $date?->getTimestamp() ?: '0'), $url);
    }

    /**
     * @return iterable<array{string|null, string, string, string}>
     */
    public static function getSalesChannelIds(): iterable
    {
        yield [null, 'https://www.anywebpage.test', 'media/78/a1/myimage.jpg', '200', null];
        yield [null, 'https://www.anyotherwebpage.test', 'media/78/a1/myimage.jpg', '200', new \DateTimeImmutable()];
        yield [Uuid::randomHex(), 'https://www.anyother2webpage.test', 'media/aa/a1/myimage.jpg', '300', new \DateTimeImmutable()];
        yield [Uuid::randomHex(), 'https://www.anyother3webpage.test', 'media/aa/bb/myimage.jpg', '700', null];
        yield [Uuid::randomHex(), 'https://www.anyother4webpage.test', 'media/aa/cc/myimage.jpg', '900', new \DateTimeImmutable()];
        yield [Uuid::randomHex(), 'https://www.anyother5webpage.test', 'media/aa/dd/myimage.jpg', '1000', null];
    }
}
