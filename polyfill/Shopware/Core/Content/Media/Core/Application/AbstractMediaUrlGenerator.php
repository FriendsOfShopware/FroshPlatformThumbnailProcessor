<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\Core\Application;

if (!\class_exists(AbstractMediaUrlGenerator::class)) {
    abstract class AbstractMediaUrlGenerator
    {
        abstract public function generate(array $paths): array;
    }
}

