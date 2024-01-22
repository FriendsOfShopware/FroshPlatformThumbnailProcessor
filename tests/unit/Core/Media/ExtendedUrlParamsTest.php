<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Core\Media;

use Frosh\ThumbnailProcessor\Core\Media\ExtendedUrlParams;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\Uuid\Uuid;

class ExtendedUrlParamsTest extends TestCase
{
    public function testFromMediaThrowsExceptionOnInvalidPath(): void
    {
        $entity = new Entity();
        $entity->assign(['path' => null]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"path" must be a string');
        ExtendedUrlParams::fromMedia($entity);
    }

    public function testFromMediaWithNotSetUpdatedAtAndCreatedAt(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
        ]);

        $result = ExtendedUrlParams::fromMedia($entity);
        static::assertNull($result->updatedAt);
    }

    public function testFromMediaWithUpdatedAtWithString(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'updatedAt' => '2021-09-01 12:00:00',
        ]);

        $result = ExtendedUrlParams::fromMedia($entity);
        static::assertNull($result->updatedAt);
    }

    public function testFromMediaWithUpdatedAtWithDateTimeInterface(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'updatedAt' => new \DateTime(),
        ]);

        $result = ExtendedUrlParams::fromMedia($entity);
        static::assertInstanceOf(\DateTimeInterface::class, $result->updatedAt);
    }

    public function testFromThumbnailThrowsExceptionOnInvalidPath(): void
    {
        $entity = new Entity();
        $entity->assign(['path' => null]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"path" must be a string');
        ExtendedUrlParams::fromThumbnail($entity);
    }

    public function testFromThumbnailWithNotSetUpdatedAtAndCreatedAt(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'width' => 100,
        ]);
        $entity->setTranslated(['mediaUrlParams' => ExtendedUrlParams::fromMedia($entity)]);

        $result = ExtendedUrlParams::fromThumbnail($entity);
        static::assertNull($result->updatedAt);
    }

    public function testFromThumbnailWithUpdatedAtWithString(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'width' => 100,
            'updatedAt' => '2021-09-01 12:00:00',
        ]);
        $entity->setTranslated(['mediaUrlParams' => ExtendedUrlParams::fromMedia($entity)]);

        $result = ExtendedUrlParams::fromThumbnail($entity);
        static::assertNull($result->updatedAt);
    }

    public function testFromThumbnailWithoutMediaUrlParamsThrowsException(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'width' => 100,
            'updatedAt' => '2021-09-01 12:00:00',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('"mediaUrlParams" within translations must be type of "%s"', ExtendedUrlParams::class));
        ExtendedUrlParams::fromThumbnail($entity);
    }

    public function testFromThumbnailWithInvalidWidthResultsInNullWidth(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'width' => 'invalid',
            'updatedAt' => '2021-09-01 12:00:00',
        ]);
        $entity->setTranslated(['mediaUrlParams' => ExtendedUrlParams::fromMedia($entity)]);

        $result = ExtendedUrlParams::fromThumbnail($entity);
        static::assertNull($result->width);
    }

    public function testFromThumbnailWithUpdatedAtWithDateTimeInterface(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'width' => 100,
            'updatedAt' => new \DateTime(),
        ]);
        $entity->setTranslated(['mediaUrlParams' => ExtendedUrlParams::fromMedia($entity)]);

        $result = ExtendedUrlParams::fromThumbnail($entity);
        static::assertInstanceOf(\DateTimeInterface::class, $result->updatedAt);
    }
}
