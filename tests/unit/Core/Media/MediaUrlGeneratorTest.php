<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Core\Media;

use Frosh\ThumbnailProcessor\Core\Media\ExtendedUrlParam;
use Frosh\ThumbnailProcessor\Core\Media\ExtendedUrlParams;
use Frosh\ThumbnailProcessor\Core\Media\MediaUrlGenerator;
use Frosh\ThumbnailProcessor\Service\ConfigReader;
use Frosh\ThumbnailProcessor\Service\ThumbnailUrlTemplateInterface;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator;
use Shopware\Core\Content\Media\Core\Params\UrlParams;
use Shopware\Core\Content\Media\Core\Params\UrlParamsSource;
use Shopware\Core\Framework\Adapter\Filesystem\PrefixFilesystem;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\Uuid\Uuid;

class MediaUrlGeneratorTest extends TestCase
{
    public function testGenerateWithUrlParam(): void
    {
        $decoratedMediaUrlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects($this->once())
            ->method('getUrl')
            ->willReturn('https://localhost/media/123.jpg?width=3000');
        $filesystem = $this->createMock(PrefixFilesystem::class);
        $filesystem->expects($this->once())
            ->method('publicUrl')
            ->willReturn('https://localhost');

        $configReader = $this->createMock(ConfigReader::class);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];
        $configReader->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $mediaUrlGenerator = new MediaUrlGenerator(
            $decoratedMediaUrlGenerator,
            $thumbnailUrlTemplate,
            $filesystem,
            $configReader
        );

        $urlParam = new UrlParams(
            '123',
            UrlParamsSource::MEDIA,
            'media/123.jpg',
            null
        );

        $generatedPaths = $mediaUrlGenerator->generate([$urlParam]);

        static::assertIsIterable($generatedPaths);
        static::assertCount(1, $generatedPaths);
        static::assertEquals('https://localhost/media/123.jpg?width=3000', current($generatedPaths));
    }

    public function testGenerateWithExtendedUrlParam(): void
    {
        $decoratedMediaUrlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects($this->once())
            ->method('getUrl')
            ->willReturn('https://localhost/media/123.jpg?width=100');
        $filesystem = $this->createMock(PrefixFilesystem::class);
        $filesystem->expects($this->once())
            ->method('publicUrl')
            ->willReturn('https://localhost');

        $configReader = $this->createMock(ConfigReader::class);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];
        $configReader->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $mediaUrlGenerator = new MediaUrlGenerator(
            $decoratedMediaUrlGenerator,
            $thumbnailUrlTemplate,
            $filesystem,
            $configReader
        );

        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'media/123.jpg',
            'width' => 100,
        ]);

        $urlParam = ExtendedUrlParams::fromMedia($entity);

        $entity->setTranslated(['mediaUrlParam' => ExtendedUrlParam::fromUrlParams($urlParam)]);

        $urlParam = ExtendedUrlParams::fromThumbnail($entity);

        $generatedPaths = $mediaUrlGenerator->generate([$urlParam]);

        static::assertIsIterable($generatedPaths);
        static::assertCount(1, $generatedPaths);
        static::assertEquals('https://localhost/media/123.jpg?width=100', current($generatedPaths));
    }

    public function testGenerateWithInactiveConfig(): void
    {
        $decoratedMediaUrlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $decoratedMediaUrlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn(['https://localhost/media/123.jpg']);

        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects($this->never())
            ->method('getUrl')
            ->willReturn('https://localhost/media/123.jpg?width=3000');
        $filesystem = $this->createMock(PrefixFilesystem::class);
        $filesystem->expects($this->never())
            ->method('publicUrl')
            ->willReturn('https://localhost');

        $configReader = $this->createMock(ConfigReader::class);

        $defaultPluginConfig = [
            'Active' => false,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];
        $configReader->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $urlParam = new UrlParams(
            '123',
            UrlParamsSource::MEDIA,
            'media/123.jpg',
            null
        );

        $mediaUrlGenerator = new MediaUrlGenerator(
            $decoratedMediaUrlGenerator,
            $thumbnailUrlTemplate,
            $filesystem,
            $configReader
        );

        $generatedPaths = $mediaUrlGenerator->generate([$urlParam]);

        static::assertIsIterable($generatedPaths);
        static::assertCount(1, $generatedPaths);
        static::assertEquals('https://localhost/media/123.jpg', current($generatedPaths));
    }

    /**
     * @dataProvider provideAllowedExtensions
     */
    public function testGenerateWithNotAllowedExtensionResultsInOriginal(string $allowList): void
    {
        $decoratedMediaUrlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $decoratedMediaUrlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn(['https://localhost/media/123.jpg']);

        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects($this->never())
            ->method('getUrl')
            ->willReturn('https://localhost/media/123.jpg?width=3000');
        $filesystem = $this->createMock(PrefixFilesystem::class);
        $filesystem->expects($this->once())
            ->method('publicUrl')
            ->willReturn('https://localhost');

        $configReader = $this->createMock(ConfigReader::class);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => $allowList,
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];
        $configReader->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $urlParam = new UrlParams(
            '123',
            UrlParamsSource::MEDIA,
            'media/123.jpg',
            null
        );

        $mediaUrlGenerator = new MediaUrlGenerator(
            $decoratedMediaUrlGenerator,
            $thumbnailUrlTemplate,
            $filesystem,
            $configReader
        );

        $generatedPaths = $mediaUrlGenerator->generate([$urlParam]);

        static::assertIsIterable($generatedPaths);
        static::assertCount(1, $generatedPaths);
        static::assertEquals('https://localhost/media/123.jpg', current($generatedPaths));
    }

    public function testMultipleGenerationsUseCachedExtensionAllowList(): void
    {
        $decoratedMediaUrlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $thumbnailUrlTemplate = $this->createMock(ThumbnailUrlTemplateInterface::class);
        $thumbnailUrlTemplate->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturn('https://localhost/media/123.jpg?width=100');
        $filesystem = $this->createMock(PrefixFilesystem::class);
        $filesystem->expects($this->once())
            ->method('publicUrl')
            ->willReturn('https://localhost');

        $configReader = $this->createMock(ConfigReader::class);

        $defaultPluginConfig = [
            'Active' => true,
            'ProcessOriginalImageMaxWidth' => '3000',
            'ExtensionsAllowList' => 'jpg',
            'ThumbnailPattern' => '{mediaUrl}/{mediaPath}?width={width}',
        ];
        $configReader->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturnCallback(function ($key) use ($defaultPluginConfig) {
                return $defaultPluginConfig[$key] ?? null;
            });

        $mediaUrlGenerator = new MediaUrlGenerator(
            $decoratedMediaUrlGenerator,
            $thumbnailUrlTemplate,
            $filesystem,
            $configReader
        );

        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'media/123.jpg',
            'width' => 100,
        ]);

        $urlParam = ExtendedUrlParams::fromMedia($entity);

        $entity->setTranslated(['mediaUrlParam' => ExtendedUrlParam::fromUrlParams($urlParam)]);

        $urlParam = ExtendedUrlParams::fromThumbnail($entity);

        $entity2 = new Entity();
        $entity2->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'media/1232.jpg',
            'width' => 100,
        ]);

        $entity2->setTranslated(['mediaUrlParam' => ExtendedUrlParam::fromUrlParams($urlParam)]);

        $urlParam2 = ExtendedUrlParams::fromThumbnail($entity);

        $generatedPaths = $mediaUrlGenerator->generate([$urlParam, $urlParam2]);

        static::assertIsIterable($generatedPaths);
        static::assertCount(2, $generatedPaths);
        static::assertEquals('https://localhost/media/123.jpg?width=100', current($generatedPaths));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideAllowedExtensions(): iterable
    {
        yield ['gif,png'];
        yield [''];
    }
}
