<?php

namespace Frosh\ThumbnailProcessor\Tests\Unit\Core\Media;

use DateTime;
use DateTimeInterface;
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
        $this->assertNull($result->updatedAt);
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
        $this->assertNull($result->updatedAt);
    }

    public function testFromMediaWithUpdatedAtWithDateTimeInterface(): void
    {
        $entity = new Entity();
        $entity->assign([
            '_uniqueIdentifier' => Uuid::randomHex(),
            'path' => 'test.txt',
            'updatedAt' => new DateTime(),
        ]);

        $result = ExtendedUrlParams::fromMedia($entity);
        $this->assertInstanceOf(DateTimeInterface::class, $result->updatedAt);
    }
}
