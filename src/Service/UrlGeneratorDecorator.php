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
    private ?string $baseUrl;

    private ?string $fallbackBaseUrl = null;

    private ?array $config;

    private SystemConfigService $systemConfigService;

    private RequestStack $requestStack;

    private ThumbnailUrlTemplateInterface $thumbnailUrlTemplate;

    private UrlGeneratorInterface $decoratedService;

    public function __construct(
        UrlGeneratorInterface $decoratedService,
        ThumbnailUrlTemplateInterface $thumbnailUrlTemplate,
        RequestStack $requestStack,
        SystemConfigService $systemConfigService,
        ?string $baseUrl = null
    ) {
        $this->decoratedService = $decoratedService;
        $this->thumbnailUrlTemplate = $thumbnailUrlTemplate;
        $this->requestStack = $requestStack;
        $this->systemConfigService = $systemConfigService;
        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
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
            '3000'
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
            (string) $thumbnail->getWidth()
        );
    }

    public function getRelativeThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        return $this->getAbsoluteThumbnailUrl($media, $thumbnail);
    }

    public function reset(): void
    {
        $this->fallbackBaseUrl = null;
        $this->config = null;
    }

    private function createFallbackUrl(): string
    {
        $request = $this->requestStack->getMainRequest();
        if ($request !== null) {
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
        return (bool) $this->getConfig('ProcessSVG');
    }

    private function canProcessOriginalImages(): bool
    {
        return (bool) $this->getConfig('ProcessOriginalImages');
    }

    private function getConfig(string $key)
    {
        if (!isset($this->config)) {
            $this->config = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config', $this->getSalesChannelId());
        }

        return $this->config[$key] ?? null;
    }

    private function getSalesChannelId(): ?string
    {
        if ($this->requestStack === null) {
            return null;
        }

        if ($this->requestStack->getMainRequest() === null) {
            return null;
        }

        return $this->requestStack->getMainRequest()->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
    }
}
