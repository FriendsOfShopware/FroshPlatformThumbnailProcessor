<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Core\Media;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\Feature;

class MediaUrlLoader
{
    public function __construct(
        private readonly MediaUrlGenerator $generator
    ) {
    }

    /**
     * @param iterable<Entity> $entities
     */
    public function loaded(iterable $entities): void
    {
        //TODO: remove check when Shopware 6.6.0 is required
        if (!self::newBehavior()) {
            return;
        }

        $mapping = $this->map($entities);

        if (empty($mapping)) {
            return;
        }

        $urls = $this->generator->generate($mapping);

        foreach ($entities as $entity) {
            if (!isset($urls[$entity->getUniqueIdentifier()])) {
                continue;
            }

            $entity->assign(['url' => $urls[$entity->getUniqueIdentifier()]]);

            if (!$entity->has('thumbnails')) {
                continue;
            }

            $thumbnails = $entity->get('thumbnails');

            if (!\is_iterable($thumbnails)) {
                continue;
            }

            foreach ($thumbnails as $thumbnail) {
                if (!($thumbnail instanceof Entity)) {
                    continue;
                }

                if (!isset($urls[$thumbnail->getUniqueIdentifier()])) {
                    continue;
                }

                $thumbnail->assign(['url' => $urls[$thumbnail->getUniqueIdentifier()]]);
            }
        }
    }

    /**
     * @param iterable<Entity> $entities
     *
     * @return array<string, ExtendedUrlParams>
     */
    private function map(iterable $entities): array
    {
        $mapped = [];

        foreach ($entities as $entity) {
            if (!$entity->has('path') || empty($entity->get('path'))) {
                continue;
            }

            // don't generate private urls
            if (!$entity->has('private') || $entity->get('private')) {
                continue;
            }

            $mapped[$entity->getUniqueIdentifier()] = ExtendedUrlParams::fromMedia($entity);

            if (!$entity->has('thumbnails')) {
                continue;
            }

            $thumbnails = $entity->get('thumbnails');

            if (!\is_iterable($thumbnails)) {
                continue;
            }

            foreach ($thumbnails as $thumbnail) {
                if (!($thumbnail instanceof Entity)) {
                    continue;
                }

                if (!$thumbnail->has('path') || empty($thumbnail->get('path'))) {
                    continue;
                }

                if (!$thumbnail->has('width')) {
                    //TODO: load it! it might be empty due to PartialDataLoading, maybe subscribe to partial.thumbnail.loaded
                    continue;
                }

                $thumbnail->addTranslated('mediaUrlParams', $mapped[$entity->getUniqueIdentifier()]);

                $mapped[$thumbnail->getUniqueIdentifier()] = ExtendedUrlParams::fromThumbnail($thumbnail);
            }
        }

        return $mapped;
    }

    private static function newBehavior(): bool
    {
        return Feature::isActive('v6.6.0.0') || Feature::isActive('media_path');
    }
}
