<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Core\Media;

use Shopware\Core\Content\Media\Core\Params\UrlParams;
use Shopware\Core\Framework\Struct\Struct;

class ExtendedUrlParam extends Struct
{
    public function __construct(
        public string $path = '',
        public ?\DateTimeInterface $updatedAt = null
    ) {
    }

    public static function fromUrlParams(UrlParams $urlParams): self
    {
        return new self(
            path: $urlParams->path,
            updatedAt: $urlParams->updatedAt
        );
    }
}
