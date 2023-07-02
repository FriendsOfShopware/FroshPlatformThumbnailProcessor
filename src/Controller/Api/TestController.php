<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Controller\Api;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\File\FileFetcher;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class TestController
{
    public const REQUEST_ATTRIBUTE_TEST_ACTIVE = 'FroshPlatformThumbnailProcessorTestActive';

    private UrlGeneratorInterface $urlGenerator;

    private EntityRepository $mediaRepository;

    private EntityRepository $mediaFolderRepository;

    private FileSaver $fileSaver;

    private FileFetcher $fileFetcher;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        EntityRepository $mediaRepository,
        EntityRepository $mediaFolderRepository,
        FileSaver $fileSaver,
        FileFetcher $fileFetcher
    ) {
        $this->fileFetcher = $fileFetcher;
        $this->fileSaver = $fileSaver;
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->mediaRepository = $mediaRepository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/api/_action/thumbnail-processor-test/get-sample-image")
     */
    public function check(Request $request, RequestDataBag $dataBag): JsonResponse
    {
        if (!$dataBag->has('salesChannelId')) {
            return new JsonResponse(['success' => false]);
        }

        $testFile = \realpath(__DIR__ . '/../../Resources/data/froshthumbnailprocessortestimage.jpg');

        if (!\is_string($testFile) || !\is_file($testFile)) {
            throw new \RuntimeException(\sprintf('Test file at "%s" is missing', $testFile));
        }

        $fileContent = \file_get_contents($testFile);

        if (!\is_string($fileContent)) {
            throw new \RuntimeException(\sprintf('Test file at "%s" could not be read', $testFile));
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE_TEST_ACTIVE, '1');

        $salesChannelId = $dataBag->get('salesChannelId');
        if (\is_string($salesChannelId)) {
            $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID, $salesChannelId);
        }

        $media = $this->getSampleMedia($fileContent, $testFile);

        $thumbnail = new MediaThumbnailEntity();
        $thumbnail->setWidth(200);
        $thumbnail->setHeight(200);

        return new JsonResponse(['url' => $this->urlGenerator->getAbsoluteThumbnailUrl($media, $thumbnail)]);
    }

    private function getProductFolderId(Context $context): string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('media_folder.defaultFolder.entity', 'product'))
            ->addAssociation('defaultFolder')
            ->setLimit(1);

        $ids = $this->mediaFolderRepository
            ->searchIds($criteria, $context)
            ->getIds();

        if (\is_string($ids[0])) {
            return $ids[0];
        }

        throw new \RuntimeException('Media folder for product could not have been found!');
    }

    private function getSampleMedia(string $fileContent, string $testFile): MediaEntity
    {
        $context = Context::createDefaultContext();
        $mediaId = \md5($fileContent);

        $existingMedia = $this->getMediaById($mediaId, $context);
        if ($existingMedia) {
            return $existingMedia;
        }

        $mediaFolderId = $this->getProductFolderId($context);

        $this->mediaRepository->upsert(
            [
                [
                    'id' => $mediaId,
                    'mediaFolderId' => $mediaFolderId,
                ],
            ],
            $context
        );

        $pathInfo = pathinfo($testFile);
        if (empty($pathInfo['extension'])) {
            $pathInfo['extension'] = 'jpg';
        }

        $uploadedFile = $this->fileFetcher->fetchBlob(
            $fileContent,
            $pathInfo['extension'],
            'image/' . $pathInfo['extension']
        );

        $this->fileSaver->persistFileToMedia(
            $uploadedFile,
            $pathInfo['filename'],
            $mediaId,
            $context
        );

        $existingMedia = $this->getMediaById($mediaId, $context);
        if ($existingMedia) {
            return $existingMedia;
        }

        throw new \RuntimeException('Media has not been saved!');
    }

    private function getMediaById(string $id, Context $context): ?MediaEntity
    {
        $criteria = new Criteria([$id]);

        return $this->mediaRepository->search($criteria, $context)->getEntities()->first();
    }
}
