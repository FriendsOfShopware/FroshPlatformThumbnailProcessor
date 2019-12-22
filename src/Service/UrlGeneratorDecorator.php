<?php

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\Exception\EmptyMediaFilenameException;
use Shopware\Core\Content\Media\Exception\EmptyMediaIdException;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\PathnameStrategy\PathnameStrategyInterface;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
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
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var string|null
     */
    private $thumbnailPattern;

    public function __construct(
        UrlGeneratorInterface $decoratedService,
        SystemConfigService $systemConfigService,
        PathnameStrategyInterface $pathnameStrategy,
        RequestStack $requestStack,
        ?string $baseUrl = null
    )
    {
        $this->decoratedService = $decoratedService;
        $this->pathnameStrategy = $pathnameStrategy;
        $this->requestStack = $requestStack;

        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
        $this->systemConfigService = $systemConfigService;
        $this->thumbnailPattern = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern');
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
     */
    public function getAbsoluteThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        return $this->getUrl($this->getRelativeMediaUrl($media), $thumbnail->getWidth(), $thumbnail->getHeight());
    }

    /**
     * @param MediaEntity $media
     * @param MediaThumbnailEntity $thumbnail
     * @return string
     */
    public function getRelativeThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        return $this->decoratedService->getRelativeThumbnailUrl($media, $thumbnail);
    }

    private function normalizeBaseUrl(?string $baseUrl)
    {
        if (!$baseUrl) {
            return null;
        }

        return rtrim($baseUrl, '/');
    }

    public function getUrl($mediaPath, $width, $height)
    {
        $result = $this->thumbnailPattern;
        $result = str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{height}'],
            [$this->getBaseUrl(), $mediaPath, $width, $height], $result);
        return $result;
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
