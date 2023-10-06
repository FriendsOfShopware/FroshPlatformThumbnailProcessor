<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Core\Media;

use Shopware\Core\Content\Media\Core\Params\UrlParamsSource;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\Struct\Struct;

class ExtendedUrlParams extends Struct
{
    public function __construct(
        public readonly string $id,
        public readonly UrlParamsSource $source,
        public readonly string $path,
        public readonly ?\DateTimeInterface $updatedAt = null,
        public readonly ?ExtendedUrlParams $mediaUrlParams = null,
        public readonly ?int $width = null,
    ) {
    }

    public static function fromMedia(Entity $entity): self
    {
        return new self(
            id: $entity->getUniqueIdentifier(),
            source: UrlParamsSource::MEDIA,
            path: $entity->get('path'),
            updatedAt: $entity->get('updatedAt') ?? $entity->get('createdAt')
        );
    }

    public static function fromThumbnail(Entity $entity, ExtendedUrlParams $mediaUrlParams): self
    {
        return new self(
            id: $entity->getUniqueIdentifier(),
            source: UrlParamsSource::THUMBNAIL,
            path: $entity->get('path'),
            updatedAt: $entity->get('updatedAt') ?? $entity->get('createdAt'),
            mediaUrlParams: $mediaUrlParams,
            width: $entity->get('width'),
        );
    }
}
