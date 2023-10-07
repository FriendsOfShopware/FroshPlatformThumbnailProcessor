<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Core\Media;

use Shopware\Core\Content\Media\Core\Params\UrlParams;
use Shopware\Core\Content\Media\Core\Params\UrlParamsSource;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ExtendedUrlParams extends UrlParams
{
    public ?ExtendedUrlParams $mediaUrlParams = null;

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

        $mediaUrlParams = $entity->getTranslation('mediaUrlParams');
        if (!($mediaUrlParams instanceof ExtendedUrlParams)) {
            throw new \InvalidArgumentException(
                \sprintf('"mediaUrlParams" must be type of "%s"', ExtendedUrlParams::class)
            );
        }

        $updatedAt = $entity->get('updatedAt') ?? $entity->get('createdAt');
        if (!($updatedAt instanceof \DateTimeInterface)) {
            $updatedAt = null;
        }

        $width = $entity->get('width');
        if (!\is_int($width)) {
            $width = null;
        }

        $result = new self(
            id: $entity->getUniqueIdentifier(),
            source: UrlParamsSource::THUMBNAIL,
            path: $path,
            updatedAt: $updatedAt,
        );

        $result->mediaUrlParams = $mediaUrlParams;
        $result->width = $width;

        return $result;
    }
}
