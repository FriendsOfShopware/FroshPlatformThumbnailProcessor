<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnailSize\MediaThumbnailSizeCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ThumbnailServiceDecorator extends ThumbnailService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $thumbnailRepository;

    /**
     * @var FilesystemInterface
     */
    private $filesystemPublic;

    /**
     * @var FilesystemInterface
     */
    private $filesystemPrivate;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EntityRepositoryInterface
     */
    private $mediaFolderRepository;

    public function __construct(
        EntityRepositoryInterface $mediaRepository,
        EntityRepositoryInterface $thumbnailRepository,
        FilesystemInterface $fileSystemPublic,
        FilesystemInterface $fileSystemPrivate,
        UrlGeneratorInterface $urlGenerator,
        EntityRepositoryInterface $mediaFolderRepository
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->thumbnailRepository = $thumbnailRepository;
        $this->filesystemPublic = $fileSystemPublic;
        $this->filesystemPrivate = $fileSystemPrivate;
        $this->urlGenerator = $urlGenerator;
        $this->mediaFolderRepository = $mediaFolderRepository;
        parent::__construct(
            $this->mediaFolderRepository,
            $this->thumbnailRepository,
            $this->filesystemPublic,
            $this->filesystemPrivate,
            $this->urlGenerator,
            $this->mediaFolderRepository
        );
    }

    public function generateThumbnails(MediaEntity $media, Context $context): int
    {
        if (!$this->checkMediaCanHaveThumbnails($media, $context)) {
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

        $mediaThumbnailSizes = $config->getMediaThumbnailSizes();
        if ($mediaThumbnailSizes === null) {
            return 0;
        }

        /** @var MediaThumbnailCollection $toBeDeletedThumbnails */
        $toBeDeletedThumbnails = $media->getThumbnails();
        $this->thumbnailRepository->delete($toBeDeletedThumbnails->getIds(), $context);

        return $this->createThumbnailsForSizes($media, $mediaThumbnailSizes, $context);
    }

    /*
     * we don't creating thumbnail-files, just updating Repository
     */
    public function updateThumbnails(MediaEntity $media, Context $context): int
    {
        if (!$this->checkMediaCanHaveThumbnails($media, $context)) {
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

        $mediaThumbnailSizes = $config->getMediaThumbnailSizes();
        if ($mediaThumbnailSizes === null) {
            return 0;
        }

        $thumbnails = $media->getThumbnails();
        if ($thumbnails === null) {
            return 0;
        }

        $tobBeCreatedSizes = new MediaThumbnailSizeCollection($mediaThumbnailSizes->getElements());
        $toBeDeletedThumbnails = new MediaThumbnailCollection($thumbnails->getElements());

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

        return count($tobBeCreatedSizes);
    }

    private function checkMediaCanHaveThumbnails($media, $context): bool
    {
        if (!$this->mediaCanHaveThumbnails($media, $context)) {
            $this->deleteAssociatedThumbnails($media, $context);

            return false;
        }

        return true;
    }

    /*
     * we don't creating thumbnail-files, just updating Repository
     */
    private function createThumbnailsForSizes(
        MediaEntity $media,
        MediaThumbnailSizeCollection $thumbnailSizes,
        Context $context
    ): int {
        if ($thumbnailSizes->count() === 0) {
            return 0;
        }

        $savedThumbnails = [];

        try {
            foreach ($thumbnailSizes as $size) {
                $savedThumbnails[] = [
                    'width' => $size->getWidth(),
                    'height' => $size->getHeight(),
                ];
            }
        } finally {
            $mediaData = [
                'id' => $media->getId(),
                'thumbnails' => $savedThumbnails,
            ];

            $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($mediaData): void {
                $this->mediaRepository->update([$mediaData], $context);
            });

            return count($savedThumbnails);
        }
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
        if ($thumbnails === null) {
            return;
        }

        $associatedThumbnails = $thumbnails->getIds();
        $this->thumbnailRepository->delete($associatedThumbnails, $context);
    }
}
