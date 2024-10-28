<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Core\Media;

use Shopware\Core\Content\Media\Core\Params\UrlParams;
use Shopware\Core\Content\Media\Core\Params\UrlParamsSource;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ExtendedUrlParams extends UrlParams
{
    public ?int $width = null;

    public static function fromMedia(Entity $entity): self
    {
        $path = $entity->get('path');
        if (!\is_string($path)) {
            throw new \InvalidArgumentException('"path" must be a string');
        }

        $updatedAt = $entity->get('updatedAt') ?? $entity->get('createdAt');
        if (!($updatedAt instanceof \DateTimeInterface)) {
            $updatedAt = null;
        }

        return new self(
            id: $entity->getUniqueIdentifier(),
            source: UrlParamsSource::MEDIA,
            path: $path,
            updatedAt: $updatedAt
        );
    }

    public static function fromThumbnail(Entity $entity): self
    {
        $path = $entity->get('path');
        if (!\is_string($path)) {
            throw new \InvalidArgumentException('"path" must be a string');
        }

        $updatedAt = $entity->get('updatedAt') ?? $entity->get('createdAt');
        if (!($updatedAt instanceof \DateTimeInterface)) {
            $updatedAt = null;
        }

        $mediaUrlParam = $entity->getTranslation('mediaUrlParam');
        if (!($mediaUrlParam instanceof ExtendedUrlParam)) {
            throw new \InvalidArgumentException(
                \sprintf('"mediaUrlParam" within translations must be type of "%s"', self::class)
            );
        }

        $width = $entity->get('width');
        if (!\is_int($width)) {
            $width = null;
        }

        $result = new self(
            id: $entity->getUniqueIdentifier(),
            source: UrlParamsSource::THUMBNAIL,
            path: $mediaUrlParam->path,
            updatedAt: $mediaUrlParam->updatedAt
        );

        $result->width = $width;

        return $result;
    }
}
