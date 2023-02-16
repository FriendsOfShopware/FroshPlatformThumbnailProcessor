<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use League\Flysystem\FilesystemOperator;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\Aggregate\MediaFolderConfiguration\MediaFolderConfigurationEntity;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnailSize\MediaThumbnailSizeCollection;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ThumbnailServiceDecorator extends ThumbnailService
{
    private readonly EntityRepository $thumbnailRepository;

    private readonly EntityRepository $mediaFolderRepository;

    public function __construct(
        EntityRepository $thumbnailRepository,
        FilesystemOperator $fileSystemPublic,
        FilesystemOperator $fileSystemPrivate,
        UrlGeneratorInterface $urlGenerator,
        EntityRepository $mediaFolderRepository
    ) {
        parent::__construct(
            $thumbnailRepository,
            $fileSystemPublic,
            $fileSystemPrivate,
            $urlGenerator,
            $mediaFolderRepository
        );

        $this->thumbnailRepository = $thumbnailRepository;
        $this->mediaFolderRepository = $mediaFolderRepository;
    }

    public function generate(MediaCollection $collection, Context $context): int
    {
        $delete = [];

        $generate = [];

        /** @var MediaEntity $media */
        foreach ($collection as $media) {
            if ($media->getThumbnails() === null) {
                throw new \RuntimeException('Thumbnail association not loaded - please pre load media thumbnails');
            }

            if (!$this->mediaCanHaveThumbnails($media, $context)) {
                $delete = [...$delete, ...$media->getThumbnails()->getIds()];

                continue;
            }

            $mediaFolder = $media->getMediaFolder();
            if (!$mediaFolder instanceof MediaFolderEntity) {
                continue;
            }

            $config = $mediaFolder->getConfiguration();
            if (!$config instanceof MediaFolderConfigurationEntity) {
                continue;
            }

            $delete = [...$delete, ...$media->getThumbnails()->getIds()];

            $generate[] = $media;
        }

        $updates = [];

        /** @var MediaEntity $media */
        foreach ($generate as $media) {
            if ($media->getMediaFolder() === null || $media->getMediaFolder()->getConfiguration() === null) {
                continue;
            }

            $config = $media->getMediaFolder()->getConfiguration();
            if (!$config instanceof MediaFolderConfigurationEntity) {
                continue;
            }

            $thumbnailSizes = $config->getMediaThumbnailSizes();
            if (!$thumbnailSizes instanceof MediaThumbnailSizeCollection) {
                continue;
            }

            $thumbnails = $this->createThumbnailsForSizes($media, $thumbnailSizes);

            foreach ($thumbnails as $thumbnail) {
                $updates[] = $thumbnail;
            }
        }

        $updates = array_values(array_filter($updates));

        if ($delete !== []) {
            $this->thumbnailRepository->delete($delete, $context);
        }

        if ($updates === []) {
            return 0;
        }

        $context->scope(Context::SYSTEM_SCOPE, function ($context) use ($updates): void {
            $this->thumbnailRepository->create($updates, $context);
        });

        return \count($updates);
    }

    /**
     * @deprecated tag:v6.5.0 - Use `generate` instead
     */
    public function generateThumbnails(MediaEntity $media, Context $context): int
    {
        if (!$this->checkMediaCanHaveThumbnails($media, $context)) {
            return 0;
        }

        $mediaFolder = $media->getMediaFolder();
        if (!$mediaFolder instanceof MediaFolderEntity) {
            return 0;
        }

        $config = $mediaFolder->getConfiguration();
        if (!$config instanceof MediaFolderConfigurationEntity) {
            return 0;
        }

        $mediaThumbnailSizes = $config->getMediaThumbnailSizes();
        if (!$mediaThumbnailSizes instanceof MediaThumbnailSizeCollection) {
            return 0;
        }

        /** @var MediaThumbnailCollection $toBeDeletedThumbnails */
        $toBeDeletedThumbnails = $media->getThumbnails();
        $this->thumbnailRepository->delete($toBeDeletedThumbnails->getIds(), $context);

        $update = $this->createThumbnailsForSizes($media, $mediaThumbnailSizes);

        if ($update === []) {
            return 0;
        }

        $context->scope(Context::SYSTEM_SCOPE, function ($context) use ($update): void {
            $this->thumbnailRepository->create($update, $context);
        });

        return \count($update);
    }

    /*
     * we don't creating thumbnail-files, just updating Repository
     */
    public function updateThumbnails(MediaEntity $media, Context $context, bool $strict = false): int
    {
        if (!$this->checkMediaCanHaveThumbnails($media, $context)) {
            return 0;
        }

        $mediaFolder = $media->getMediaFolder();
        if (!$mediaFolder instanceof MediaFolderEntity) {
            return 0;
        }

        $config = $mediaFolder->getConfiguration();
        if (!$config instanceof MediaFolderConfigurationEntity) {
            return 0;
        }

        $tobBeCreatedSizes = new MediaThumbnailSizeCollection($config->getMediaThumbnailSizes()?->getElements() ?? []);
        $toBeDeletedThumbnails = new MediaThumbnailCollection($media->getThumbnails()?->getElements() ?? []);

        foreach ($tobBeCreatedSizes as $thumbnailSize) {
            foreach ($toBeDeletedThumbnails as $thumbnail) {
                if ($thumbnail->getWidth() === $thumbnailSize->getWidth()
                    && $thumbnail->getHeight() === $thumbnailSize->getHeight()
                ) {
                    $toBeDeletedThumbnails->remove($thumbnail->getId());
                    $tobBeCreatedSizes->remove($thumbnailSize->getId());

                    continue 2;
                }
            }
        }

        $this->thumbnailRepository->delete($toBeDeletedThumbnails->getIds(), $context);

        $update = $this->createThumbnailsForSizes($media, $tobBeCreatedSizes);

        if ($update === []) {
            return 0;
        }

        $context->scope(Context::SYSTEM_SCOPE, function ($context) use ($update): void {
            $this->thumbnailRepository->create($update, $context);
        });

        return \count($update);
    }

    private function checkMediaCanHaveThumbnails(MediaEntity $media, Context $context): bool
    {
        if (!$this->mediaCanHaveThumbnails($media, $context)) {
            $this->deleteAssociatedThumbnails($media, $context);

            return false;
        }

        return true;
    }

    /*
     * we don't create thumbnail-files!
     */
    private function createThumbnailsForSizes(
        MediaEntity $media,
        MediaThumbnailSizeCollection $thumbnailSizes
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

    private function ensureConfigIsLoaded(MediaEntity $media, Context $context): void
    {
        $mediaFolderId = $media->getMediaFolderId();

        if (!$mediaFolderId) {
            return;
        }

        if ($media->getMediaFolder() !== null) {
            return;
        }

        $criteria = new Criteria([$mediaFolderId]);
        $criteria->addAssociation('configuration.mediaThumbnailSizes');
        /** @var MediaFolderEntity $folder */
        $folder = $this->mediaFolderRepository->search($criteria, $context)->get($mediaFolderId);
        $media->setMediaFolder($folder);
    }

    private function mediaCanHaveThumbnails(MediaEntity $media, Context $context): bool
    {
        if (!$media->hasFile()) {
            return false;
        }

        if (!$this->thumbnailsAreGeneratable($media)) {
            return false;
        }

        $this->ensureConfigIsLoaded($media, $context);

        if ($media->getMediaFolder() === null || $media->getMediaFolder()->getConfiguration() === null) {
            return false;
        }

        return $media->getMediaFolder()->getConfiguration()->getCreateThumbnails();
    }

    private function thumbnailsAreGeneratable(MediaEntity $media): bool
    {
        return $media->getMediaType() instanceof ImageType
            && !$media->getMediaType()->is(ImageType::VECTOR_GRAPHIC)
            && !$media->getMediaType()->is(ImageType::ANIMATED);
    }

    private function deleteAssociatedThumbnails(MediaEntity $media, Context $context): void
    {
        $thumbnails = $media->getThumbnails();
        if (!$thumbnails instanceof MediaThumbnailCollection) {
            return;
        }

        $associatedThumbnails = $thumbnails->getIds();
        $this->thumbnailRepository->delete($associatedThumbnails, $context);
    }
}
