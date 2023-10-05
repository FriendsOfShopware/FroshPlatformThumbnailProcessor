<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\Core\Application;

use Shopware\Core\Content\Media\Core\Params\UrlParams;

//TODO: remove if min version is 6.6.0
if (!\class_exists(AbstractMediaUrlGenerator::class, false)) {
    abstract class AbstractMediaUrlGenerator
    {
        /**
         * @param array<string|int, UrlParams> $paths indexed by id, value contains the path
         * @return array<string|int, string> indexed by id, value contains the absolute url (e.g. https://my.shop.de/media/0a/test.jpg)
         */
        abstract public function generate(array $paths): array;
    }
}

