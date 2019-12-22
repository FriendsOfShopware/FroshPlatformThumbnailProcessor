<?php

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\Exception\EmptyMediaFilenameException;
use Shopware\Core\Content\Media\Exception\EmptyMediaIdException;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\PathnameStrategy\PathnameStrategyInterface;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGeneratorDecorator implements UrlGeneratorInterface
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var PathnameStrategyInterface
     */
    private $pathnameStrategy;

    /**
     * @var UrlGeneratorInterface
     */
    private $decoratedService;

    public function __construct(
        UrlGeneratorInterface $decoratedService,
        PathnameStrategyInterface $pathnameStrategy,
        RequestStack $requestStack,
        ?string $baseUrl = null
    ) {
        $this->decoratedService = $decoratedService;
        $this->pathnameStrategy = $pathnameStrategy;
        $this->requestStack = $requestStack;

        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
    }

    public function getAbsoluteMediaUrl(MediaEntity $media): string
    {
        return $this->decoratedService->getAbsoluteMediaUrl($media);
    }

    public function getRelativeMediaUrl(MediaEntity $media): string
    {
        return $this->decoratedService->getRelativeMediaUrl($media);
    }

    /**
     * @param MediaEntity $media
     * @param MediaThumbnailEntity $thumbnail
     * @return string
     * @throws EmptyMediaFilenameException
     * @throws EmptyMediaIdException
     */
    public function getAbsoluteThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        return $this->getBaseUrl() . '/' . $this->getRelativeThumbnailUrl($media, $thumbnail);
    }

    /**
     * @param MediaEntity $media
     * @param MediaThumbnailEntity $thumbnail
     * @return string
     * @throws EmptyMediaFilenameException
     * @throws EmptyMediaIdException
     */
    public function getRelativeThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        $this->validateMedia($media);

        return $this->toPathString([
            'media',
            $this->pathnameStrategy->generatePathHash($media),
            $this->pathnameStrategy->generatePathCacheBuster($media),
            $this->pathnameStrategy->generatePhysicalFilename($media) . '?width='.$thumbnail->getWidth() . '&height='.$thumbnail->getHeight(),
        ]);
    }

    private function normalizeBaseUrl(?string $baseUrl)
    {
        if (!$baseUrl) {
            return null;
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * @param MediaEntity $media
     * @throws EmptyMediaFilenameException
     * @throws EmptyMediaIdException
     */
    private function validateMedia(MediaEntity $media): void
    {
        if (empty($media->getId())) {
            throw new EmptyMediaIdException();
        }

        if (empty($media->getFileName())) {
            throw new EmptyMediaFilenameException();
        }
    }

    private function toPathString(array $parts): string
    {
        return implode('/', array_filter($parts));
    }

    private function getBaseUrl()
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->createFallbackUrl();
        }

        return $this->baseUrl;
    }

    private function createFallbackUrl()
    {
        $request = $this->requestStack->getMasterRequest();
        if ($request) {
            $basePath = $request->getSchemeAndHttpHost() . $request->getBasePath();

            return rtrim($basePath, '/');
        }

        return '';
    }
}
