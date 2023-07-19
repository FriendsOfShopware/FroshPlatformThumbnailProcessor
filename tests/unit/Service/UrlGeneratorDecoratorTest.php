<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\TestsUnit\Service;

use Frosh\ThumbnailProcessor\Service\ConfigReader;
use Frosh\ThumbnailProcessor\Service\ThumbnailUrlTemplateInterface;
use Frosh\ThumbnailProcessor\Service\UrlGeneratorDecorator;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGeneratorDecoratorTest extends TestCase
{
    public function testGetRelativeThumbnailUrl(): void
    {
        [$urlGenerator, $media, $thumbnail] = $this->mockMediaThumbnailData(
            decoratedUrlGenerator: $this->getDefaultDecoratedUrlGenerator()
        );

        $result = $urlGenerator->getRelativeThumbnailUrl($media, $thumbnail);

        static::assertSame('https://any.url/aa/bb/cc.jpg?width=100', $result);
    }

    public function testGetAbsoluteMediaUrl(): void
    {
        [$urlGenerator, $media] = $this->mockMediaThumbnailData(
            decoratedUrlGenerator: $this->getDefaultDecoratedUrlGenerator()
        );

        $result = $urlGenerator->getAbsoluteMediaUrl($media);

        static::assertSame('https://any.url/aa/bb/cc.jpg?width=100', $result);
    }

    public function testGetAbsoluteMediaUrlWithInactiveConfig(): void
    {
        $decoratedUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $decoratedUrlGenerator->expects(static::once())
            ->method('getAbsoluteMediaUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg');

        [$urlGenerator, $media] = $this->mockMediaThumbnailData(
            decoratedUrlGenerator: $decoratedUrlGenerator,
            pluginConfig: ['Active' => false]
        );

        $result = $urlGenerator->getAbsoluteMediaUrl($media);

        static::assertSame('https://any.url/aa/bb/cc.jpg', $result);
    }

    public function testGetAbsoluteMediaUrlWithUnsupportedFileType(): void
    {
        $decoratedUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $decoratedUrlGenerator->expects(static::once())
            ->method('getAbsoluteMediaUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg');

        [$urlGenerator, $media] = $this->mockMediaThumbnailData(
            decoratedUrlGenerator: $decoratedUrlGenerator,
            pluginConfig: ['ExtensionsAllowList' => 'png, gif']
        );

        $result = $urlGenerator->getAbsoluteMediaUrl($media);

        static::assertSame('https://any.url/aa/bb/cc.jpg', $result);
    }

    public function testGetAbsoluteMediaUrlWithCachedExtensionsAllowList(): void
    {
        $decoratedUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $decoratedUrlGenerator->expects(static::never())
            ->method('getAbsoluteMediaUrl');

        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects(static::any())
            ->method('getUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg?width=100');

        $fileSystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'https://any.url']);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::exactly(5))
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $urlGenerator = new UrlGeneratorDecorator(
            $decoratedUrlGenerator,
            $thumbnailUrlTemplate,
            $fileSystem,
            $configReader
        );

        $media = $this->createMediaEntity();

        $first = $urlGenerator->getAbsoluteMediaUrl($media);
        $second = $urlGenerator->getAbsoluteMediaUrl($media);

        static::assertSame($first, $second);
    }

    public function testGetAbsoluteMediaUrlWithEmptyFileExtension(): void
    {
        $decoratedUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $decoratedUrlGenerator->expects(static::once())
            ->method('getAbsoluteMediaUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg');

        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects(static::any())
            ->method('getUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg?width=100');

        $fileSystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'https://any.url']);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::once())
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $urlGenerator = new UrlGeneratorDecorator(
            $decoratedUrlGenerator,
            $thumbnailUrlTemplate,
            $fileSystem,
            $configReader
        );

        $mediaWithoutfileExtension = new MediaEntity();
        $mediaWithoutfileExtension->setId('test');
        $mediaWithoutfileExtension->setFileName('test.jpg');
        $mediaWithoutfileExtension->setMimeType('image/jpg');
        $mediaWithoutfileExtension->setFileSize(100);
        $mediaWithoutfileExtension->setCreatedAt(new \DateTime());
        $mediaWithoutfileExtension->setUpdatedAt(new \DateTime());
        $mediaWithoutfileExtension->setMediaFolderId('test');
        $mediaWithoutfileExtension->setMediaType(new ImageType());

        $result = $urlGenerator->getAbsoluteMediaUrl($mediaWithoutfileExtension);

        static::assertSame('https://any.url/aa/bb/cc.jpg', $result);
    }

    public function testGetAbsoluteMediaUrlWithEmptyExtensionsAllowList(): void
    {
        $decoratedUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $decoratedUrlGenerator->expects(static::once())
            ->method('getAbsoluteMediaUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg');

        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects(static::any())
            ->method('getUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg?width=100');

        $fileSystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'https://any.url']);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => '',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::any())
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $urlGenerator = new UrlGeneratorDecorator(
            $decoratedUrlGenerator,
            $thumbnailUrlTemplate,
            $fileSystem,
            $configReader
        );

        $media = $this->createMediaEntity();

        $result = $urlGenerator->getAbsoluteMediaUrl($media);

        static::assertSame('https://any.url/aa/bb/cc.jpg', $result);
    }

    public function testGetRelativeMediaUrl(): void
    {
        static::assertTrue(true);
        [$urlGenerator, $media] = $this->mockMediaThumbnailData(
            decoratedUrlGenerator: $this->getDefaultDecoratedUrlGenerator()
        );

        $result = $urlGenerator->getRelativeMediaUrl($media);

        static::assertSame('aa/bb/cc.jpg', $result);
    }

    public function testGetAbsoluteThumbnailUrl(): void
    {
        [$urlGenerator, $media, $thumbnail] = $this->mockMediaThumbnailData(
            decoratedUrlGenerator: $this->getDefaultDecoratedUrlGenerator()
        );

        $result = $urlGenerator->getAbsoluteThumbnailUrl($media, $thumbnail);

        static::assertSame('https://any.url/aa/bb/cc.jpg?width=100', $result);
    }

    public function testGetAbsoluteThumbnailUrlWithInactiveConfig(): void
    {
        $decoratedUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $decoratedUrlGenerator->expects(static::once())
            ->method('getAbsoluteMediaUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg');

        [$urlGenerator, $media, $thumbnail] = $this->mockMediaThumbnailData(
            decoratedUrlGenerator: $decoratedUrlGenerator,
            pluginConfig: ['Active' => false]
        );

        $result = $urlGenerator->getAbsoluteThumbnailUrl($media, $thumbnail);

        static::assertSame('https://any.url/aa/bb/cc.jpg', $result);
    }

    public function testReset(): void
    {
        $decoratedUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $decoratedUrlGenerator->expects(static::never())
            ->method('getAbsoluteMediaUrl');

        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects(static::exactly(2))
            ->method('getUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg?width=100');

        $fileSystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'https://any.url']);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::exactly(6))
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $urlGenerator = new UrlGeneratorDecorator(
            $decoratedUrlGenerator,
            $thumbnailUrlTemplate,
            $fileSystem,
            $configReader
        );

        $media = $this->createMediaEntity();

        $first = $urlGenerator->getAbsoluteMediaUrl($media);
        $urlGenerator->reset();
        $second = $urlGenerator->getAbsoluteMediaUrl($media);

        static::assertSame($first, $second);
    }

    /**
     * @param array<string, mixed>|null $pluginConfig
     *
     * @return array{UrlGeneratorDecorator, MediaEntity, MediaThumbnailEntity}
     */
    private function mockMediaThumbnailData(UrlGeneratorInterface $decoratedUrlGenerator, ?array $pluginConfig = null): array
    {
        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];

        if (\is_array($pluginConfig)) {
            $defaultPluginConfig = \array_merge($defaultPluginConfig, $pluginConfig);
        }

        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects(static::any())
            ->method('getUrl')
            ->willReturn('https://any.url/aa/bb/cc.jpg?width=100');

        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $fileSystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'https://any.url']);

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->expects(static::any())
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $urlGenerator = new UrlGeneratorDecorator(
            $decoratedUrlGenerator,
            $thumbnailUrlTemplate,
            $fileSystem,
            $configReader
        );

        $media = $this->createMediaEntity();
        $thumbnail = $this->createThumbnailEntity();

        return [$urlGenerator, $media, $thumbnail];
    }

    private function createMediaEntity(): MediaEntity
    {
        $media = new MediaEntity();
        $media->setId('test');
        $media->setFileExtension('jpg');
        $media->setFileName('test.jpg');
        $media->setMimeType('image/jpg');
        $media->setFileSize(100);
        $media->setCreatedAt(new \DateTime());
        $media->setUpdatedAt(new \DateTime());
        $media->setMediaFolderId('test');
        $media->setMediaType(new ImageType());

        return $media;
    }

    private function createThumbnailEntity(): MediaThumbnailEntity
    {
        $thumbnail = new MediaThumbnailEntity();
        $thumbnail->setId('test');
        $thumbnail->setMediaId('test');
        $thumbnail->setMedia($this->createMediaEntity());
        $thumbnail->setWidth(100);
        $thumbnail->setHeight(100);
        $thumbnail->setCreatedAt(new \DateTime());
        $thumbnail->setUpdatedAt(new \DateTime());

        return $thumbnail;
    }

    private function getDefaultDecoratedUrlGenerator(): UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(static::never())
            ->method('getAbsoluteMediaUrl')
            ->willReturn('');
        $urlGenerator->expects(static::any())
            ->method('getRelativeMediaUrl')
            ->willReturn('aa/bb/cc.jpg');

        return $urlGenerator;
    }
}
