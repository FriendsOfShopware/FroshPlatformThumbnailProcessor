<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\Core\Application;

use Shopware\Core\Content\Media\Core\Params\UrlParams;

if (!\class_exists(AbstractMediaUrlGenerator::class, false)) {
    /**
     * @param array<string|int, UrlParams> $paths indexed by id, value contains the path
     *
     * @return array<string|int, string> indexed by id, value contains the absolute url (e.g. https://my.shop.de/media/0a/test.jpg)
     */
    abstract class AbstractMediaUrlGenerator
    {
        abstract public function generate(array $paths): array;
    }
}

