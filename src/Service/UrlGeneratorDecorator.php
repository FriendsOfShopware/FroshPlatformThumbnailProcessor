<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ResetInterface;

class UrlGeneratorDecorator implements UrlGeneratorInterface, ResetInterface
{
    private RequestStack $requestStack;

    private ?string $baseUrl;

    private UrlGeneratorInterface $decoratedService;

    private ThumbnailUrlTemplateInterface $thumbnailUrlTemplate;

    private ?bool $processSVG = null;

    private ?bool $processOriginalImages = null;

    private SystemConfigService $systemConfigService;

    private ?string $fallbackBaseUrl = null;

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

    public function reset(): void
    {
        $this->fallbackBaseUrl = null;
    }

    private function createFallbackUrl(): string
    {
        $request = $this->requestStack->getMainRequest();
        if ($request) {
            $basePath = $request->getSchemeAndHttpHost() . $request->getBasePath();

            return rtrim($basePath, '/');
        }

        return (string) EnvironmentHelper::getVariable('APP_URL');
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
            return $this->fallbackBaseUrl ?? $this->fallbackBaseUrl = $this->createFallbackUrl();
        }

        return $this->baseUrl;
    }

    private function canProcessSVG(): bool
    {
        if ($this->processSVG !== null) {
            return $this->processSVG;
        }

        $this->processSVG = (bool)$this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ProcessSVG', $this->getRequestSalesChannelId());
        return $this->processSVG;
    }

    private function canProcessOriginalImages(): bool
    {
        if ($this->processOriginalImages !== null) {
            return $this->processOriginalImages;
        }

        $this->processOriginalImages = (bool)$this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ProcessOriginalImages', $this->getRequestSalesChannelId());
        return $this->processOriginalImages;
    }

    public function getRequestSalesChannelId(): ?string
    {
        $masterRequest = $this->requestStack->getMainRequest();

        if ($masterRequest === null) {
            return null;
        }

        $salesChannelId = $masterRequest->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        if (\is_string($salesChannelId)) {
            return $salesChannelId;
        }

        return null;
    }
}
