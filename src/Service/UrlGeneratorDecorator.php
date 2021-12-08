<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
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
     * @var string|null
     */
    private $baseUrl;

    /**
     * @var UrlGeneratorInterface
     */
    private $decoratedService;

    /**
     * @var ThumbnailUrlTemplateInterface
     */
    private $thumbnailUrlTemplate;

    /**
     * @var bool|null
     */
    private $processSVG;

    /**
     * @var bool|null
     */
    private $processOriginalImages;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        UrlGeneratorInterface $decoratedService,
        ThumbnailUrlTemplateInterface $thumbnailUrlTemplate,
        RequestStack $requestStack,
        SystemConfigService $systemConfigService,
        ?string $baseUrl = null
    )
    {
        $this->decoratedService = $decoratedService;
        $this->requestStack = $requestStack;

        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);

        $this->thumbnailUrlTemplate = $thumbnailUrlTemplate;
        $this->systemConfigService = $systemConfigService;
    }

    public function getAbsoluteMediaUrl(MediaEntity $media): string
    {
        if (!($media->getMediaType() instanceof ImageType)) {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        if (!$this->canProcessOriginalImages()) {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        if (!$this->canProcessSVG() && $media->getFileExtension() === 'svg') {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        return $this->thumbnailUrlTemplate->getUrl(
            $this->getBaseUrl(),
            $this->getRelativeMediaUrl($media),
            "3000",
            "3000"
        );
    }

    public function getRelativeMediaUrl(MediaEntity $media): string
    {
        return $this->decoratedService->getRelativeMediaUrl($media);
    }

    public function getAbsoluteThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        if (!$this->canProcessSVG() && $media->getFileExtension() === 'svg') {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        return $this->thumbnailUrlTemplate->getUrl(
            $this->getBaseUrl(),
            $this->decoratedService->getRelativeMediaUrl($media),
            (string) $thumbnail->getWidth(),
            (string) $thumbnail->getHeight());
    }

    public function getRelativeThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        return $this->getAbsoluteThumbnailUrl($media, $thumbnail);
    }

    private function normalizeBaseUrl(?string $baseUrl): ?string
    {
        if (!$baseUrl) {
            return null;
        }

        return rtrim($baseUrl, '/');
    }

    private function getBaseUrl(): string
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->createFallbackUrl();
        }

        return $this->baseUrl;
    }

    private function createFallbackUrl(): string
    {
        $request = $this->requestStack->getMasterRequest();
        if ($request) {
            $basePath = $request->getSchemeAndHttpHost() . $request->getBasePath();

            return rtrim($basePath, '/');
        }

        return '';
    }

    private function canProcessSVG(): bool
    {
        if ($this->processSVG !== null) {
            return $this->processSVG;
        }

        $this->processSVG = (bool)$this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ProcessSVG');
        return $this->processSVG;
    }

    private function canProcessOriginalImages(): bool
    {
        if ($this->processOriginalImages !== null) {
            return $this->processOriginalImages;
        }

        $this->processOriginalImages = (bool)$this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ProcessOriginalImages');
        return $this->processOriginalImages;
    }
}
