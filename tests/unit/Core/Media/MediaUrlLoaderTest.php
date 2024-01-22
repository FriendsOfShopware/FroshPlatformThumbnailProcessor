<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Core\Media;

use Frosh\ThumbnailProcessor\Core\Media\MediaUrlGenerator;
use Frosh\ThumbnailProcessor\Core\Media\MediaUrlLoader;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\PartialEntity;
use Shopware\Core\Framework\Uuid\Uuid;

class MediaUrlLoaderTest extends TestCase
{
    public function testLoaded(): void
    {
        $id = Uuid::randomHex();

        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn([$id => 'https://example.com/a0/image.jpg']);

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => $id,
            'path' => 'a0/image.txt',
            'private' => false,
        ]);

        static::assertFalse($entity->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertTrue($entity->has('url'));
        static::assertSame('https://example.com/a0/image.jpg', $entity->get('url'));
    }

    public function testLoadedWithMissingPrivate(): void
    {
        $id = Uuid::randomHex();

        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::never())
            ->method('generate')
            ->willReturn([$id => 'https://example.com/a0/image.jpg']);

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => $id,
            'path' => 'a0/image.txt',
        ]);

        static::assertFalse($entity->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertFalse($entity->has('url'));
    }

    public function testLoadedWithMissingUrlResult(): void
    {
        $id = Uuid::randomHex();

        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn([]);

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => $id,
            'path' => 'a0/image.txt',
            'private' => false,
        ]);

        static::assertFalse($entity->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertFalse($entity->has('url'));
    }

    public function testLoadedWithMissingPathResult(): void
    {
        $id = Uuid::randomHex();

        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::never())
            ->method('generate');

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => $id,
            'private' => false,
        ]);

        static::assertFalse($entity->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertFalse($entity->has('url'));
    }

    public function testLoadedWithThumbnailHavingUrls(): void
    {
        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn([
                '1' => 'https://example.com/a0/image.jpg?width=100',
                '2' => 'https://example.com/a0/thumbnailimage.jpg?width=100',
            ]);

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => '1',
            'path' => 'a0/image.txt',
            'private' => false,
            'width' => 100,
            'thumbnails' => [
                (new PartialEntity())->assign([
                    'id' => '2',
                    'path' => 'a0/thumbnailimage.jpg',
                    'width' => 100,
                ]),
            ],
        ]);

        static::assertFalse($entity->has('url'));

        $thumbnails = $entity->get('thumbnails');
        static::assertIsArray($thumbnails);
        static::assertFalse($thumbnails[0]->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertTrue($entity->has('url'));

        $thumbnails = $entity->get('thumbnails');
        static::assertIsArray($thumbnails);
        static::assertTrue($thumbnails[0]->has('url'));
        static::assertSame('https://example.com/a0/thumbnailimage.jpg?width=100', $thumbnails[0]->get('url'));
    }

    public function testLoadedWithThumbnailHavingMaxWidthWithMissingWidth(): void
    {
        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn([
                '1' => 'https://example.com/a0/image.jpg?width=100',
                '2' => 'https://example.com/a0/thumbnailimage.jpg?width=100',
            ]);

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => '1',
            'path' => 'a0/image.txt',
            'private' => false,
            'thumbnails' => [
                (new PartialEntity())->assign([
                    'id' => '2',
                    'path' => 'a0/thumbnailimage.jpg',
                ]),
            ],
        ]);

        static::assertFalse($entity->has('url'));

        $thumbnails = $entity->get('thumbnails');
        static::assertIsArray($thumbnails);
        static::assertFalse($thumbnails[0]->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertTrue($entity->has('url'));

        $thumbnails = $entity->get('thumbnails');
        static::assertIsArray($thumbnails);
        static::assertTrue($thumbnails[0]->has('url'));
        static::assertSame('https://example.com/a0/thumbnailimage.jpg?width=100', $thumbnails[0]->get('url'));
    }

    public function testLoadedWithThumbnailHavingNoUrl(): void
    {
        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn([
                '1' => 'https://example.com/a0/image.jpg',
            ]);

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => '1',
            'path' => 'a0/image.txt',
            'private' => false,
            'width' => 100,
            'thumbnails' => [
                (new PartialEntity())->assign([
                    'id' => '2',
                ]),
            ],
        ]);

        static::assertFalse($entity->has('url'));

        $thumbnails = $entity->get('thumbnails');
        static::assertIsArray($thumbnails);
        static::assertFalse($thumbnails[0]->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertTrue($entity->has('url'));

        $thumbnails = $entity->get('thumbnails');
        static::assertIsArray($thumbnails);
        static::assertFalse($thumbnails[0]->has('url'));
    }

    /**
     * @dataProvider provideInvalidThumbnailData
     */
    public function testLoadedWithNotIterableThumbnail(mixed $thumbnail): void
    {
        $mediaUrlGenerator = $this->createMock(MediaUrlGenerator::class);
        $mediaUrlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn([
                '1' => 'https://example.com/a0/image.jpg',
            ]);

        $mediaUrlLoader = new MediaUrlLoader($mediaUrlGenerator);
        $entity = new PartialEntity();
        $entity->assign([
            'id' => '1',
            'path' => 'a0/image.txt',
            'private' => false,
            'width' => 100,
            'thumbnails' => $thumbnail,
        ]);

        static::assertFalse($entity->has('url'));

        $mediaUrlLoader->loaded([$entity]);

        static::assertTrue($entity->has('url'));
    }

    /**
     * @return iterable<array{mixed}>
     */
    public static function provideInvalidThumbnailData(): iterable
    {
        yield [[]];
        yield [[null]];
        yield [''];
        yield [['']];
        yield [new PartialEntity()];
    }
}