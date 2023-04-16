<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use League\Flysystem\FilesystemOperator;
use Shopware\Core\Content\Media\Aggregate\MediaFolderConfiguration\MediaFolderConfigurationEntity;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnailSize\MediaThumbnailSizeCollection;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Content\Media\Subscriber\MediaDeletionSubscriber;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

/**
 * we override ThumbnailService minimally invasive to prevent file creation
 */
class ThumbnailServiceDecorator extends ThumbnailService
{
    private readonly EntityRepository $thumbnailRepository;

    private readonly ?ThumbnailService $decorated;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(
        EntityRepository $thumbnailRepository,
        FilesystemOperator $fileSystemPublic,
        FilesystemOperator $fileSystemPrivate,
        UrlGeneratorInterface $urlGenerator,
        EntityRepository $mediaFolderRepository
    ) {
        $this->decorated = new parent(
            $thumbnailRepository,
            $fileSystemPublic,
            $fileSystemPrivate,
            $urlGenerator,
            $mediaFolderRepository
        );

        $this->thumbnailRepository = $thumbnailRepository;
    }

    /*
     * CHANGED: NOTHING! It is just a copy, to have our createThumbnailsForSizes used
     */
    public function generate(MediaCollection $collection, Context $context): int
    {
        $delete = [];

        $generate = [];

        foreach ($collection as $media) {
            if ($media->getThumbnails() === null) {
                throw new \RuntimeException('Thumbnail association not loaded - please pre load media thumbnails');
            }

            if (!$this->mediaCanHaveThumbnails($media, $context)) {
                $delete = [...$delete, ...$media->getThumbnails()->getIds()];

                continue;
            }

            $mediaFolder = $media->getMediaFolder();
            if ($mediaFolder === null) {
                continue;
            }

            $config = $mediaFolder->getConfiguration();
            if ($config === null) {
                continue;
            }

            $delete = [...$delete, ...$media->getThumbnails()->getIds()];

            $generate[] = $media;
        }

        if (!empty($delete)) {
            $context->addState(MediaDeletionSubscriber::SYNCHRONE_FILE_DELETE);

            $delete = \array_values(\array_map(fn (string $id) => ['id' => $id], $delete));

            $this->thumbnailRepository->delete($delete, $context);
        }

        $updates = [];
        foreach ($generate as $media) {
            if ($media->getMediaFolder() === null || $media->getMediaFolder()->getConfiguration() === null) {
                continue;
            }

            $config = $media->getMediaFolder()->getConfiguration();

            $thumbnails = $this->createThumbnailsForSizes($media, $config, $config->getMediaThumbnailSizes());

            foreach ($thumbnails as $thumbnail) {
                $updates[] = $thumbnail;
            }
        }

        if (empty($updates)) {
            return 0;
        }

        $context->scope(Context::SYSTEM_SCOPE, function ($context) use ($updates): void {
            $this->thumbnailRepository->create($updates, $context);
        });

        return \count($updates);
    }

    /*
     * CHANGED: we commented the strict option out
     */
    public function updateThumbnails(MediaEntity $media, Context $context, bool $strict): int
    {
        if (!$this->mediaCanHaveThumbnails($media, $context)) {
            $this->deleteAssociatedThumbnails($media, $context);

            return 0;
        }

        $mediaFolder = $media->getMediaFolder();
        if ($mediaFolder === null) {
            return 0;
        }

        $config = $mediaFolder->getConfiguration();
        if ($config === null) {
            return 0;
        }

        $strict = \func_get_args()[2] ?? false;

        if ($config->getMediaThumbnailSizes() === null) {
            return 0;
        }
        if ($media->getThumbnails() === null) {
            return 0;
        }

        $toBeCreatedSizes = new MediaThumbnailSizeCollection($config->getMediaThumbnailSizes()->getElements());
        $toBeDeletedThumbnails = new MediaThumbnailCollection($media->getThumbnails()->getElements());

        foreach ($toBeCreatedSizes as $thumbnailSize) {
            foreach ($toBeDeletedThumbnails as $thumbnail) {
                if (!$this->isSameDimension($thumbnail, $thumbnailSize)) {
                    continue;
                }

                // CHANGED: we commented this out
                /*if ($strict === true
                    && !$this->getFileSystem($media)->fileExists($this->urlGenerator->getRelativeThumbnailUrl($media, $thumbnail))) {
                    continue;
                }*/

                $toBeDeletedThumbnails->remove($thumbnail->getId());
                $toBeCreatedSizes->remove($thumbnailSize->getId());

                continue 2;
            }
        }

        $delete = \array_values(\array_map(static fn (string $id) => ['id' => $id], $toBeDeletedThumbnails->getIds()));

        $this->thumbnailRepository->delete($delete, $context);

        $update = $this->createThumbnailsForSizes($media, $config, $toBeCreatedSizes);

        if (empty($update)) {
            return 0;
        }

        $context->scope(Context::SYSTEM_SCOPE, function ($context) use ($update): void {
            $this->thumbnailRepository->create($update, $context);
        });

        return \count($update);
    }

    /*
     * CHANGED: we don't create thumbnail-files!
     */
    private function createThumbnailsForSizes(
        MediaEntity $media,
        MediaFolderConfigurationEntity $config,
        ?MediaThumbnailSizeCollection $thumbnailSizes
    ): array {
        if ($thumbnailSizes->count() === 0) {
            return [];
        }

        $savedThumbnails = [];

        foreach ($thumbnailSizes as $size) {
            $savedThumbnails[] = [
                'mediaId' => $media->getId(),
                'width' => $size->getWidth(),
                'height' => $size->getHeight(),
            ];
        }

        return $savedThumbnails;
    }

    private function deleteAssociatedThumbnails(): void
    {
        $this->callParent(__FUNCTION__, ...\func_get_args());
    }

    private function isSameDimension(): bool
    {
        return $this->callParent(__FUNCTION__, ...\func_get_args());
    }

    private function mediaCanHaveThumbnails(): bool
    {
        return $this->callParent(__FUNCTION__, ...\func_get_args());
    }

    private function callParent(string $fn): mixed
    {
        $a = clone $this->decorated;
        $args = \func_get_args();
        array_shift($args);

        return $this->bindAndCall(function () use ($a, $fn, $args) {
            return $a->$fn(...$args);
        }, $a);
    }

    private function bindAndCall(\Closure $fn, object $newThis)
    {
        $func = \Closure::bind($fn, $newThis, \get_class($newThis));

        return $func();
    }
}
