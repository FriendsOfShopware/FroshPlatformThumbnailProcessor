<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Frosh\ThumbnailProcessor\Controller\Api\TestController;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ResetInterface;

class UrlGeneratorDecorator implements UrlGeneratorInterface, ResetInterface
{
    private ?string $baseUrl;

    private ?string $fallbackBaseUrl = null;

    private ConfigReader $configReader;

    private RequestStack $requestStack;

    private ThumbnailUrlTemplateInterface $thumbnailUrlTemplate;

    private UrlGeneratorInterface $decoratedService;

    /**
     * @var array<string>|null
     */
    private ?array $extensionsAllowList = null;

    public function __construct(
        UrlGeneratorInterface $decoratedService,
        ThumbnailUrlTemplateInterface $thumbnailUrlTemplate,
        RequestStack $requestStack,
        ConfigReader $configReader,
        ?string $baseUrl = null
    ) {
        $this->decoratedService = $decoratedService;
        $this->thumbnailUrlTemplate = $thumbnailUrlTemplate;
        $this->requestStack = $requestStack;
        $this->configReader = $configReader;
        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
    }

    public function getAbsoluteMediaUrl(MediaEntity $media): string
    {
        if ($this->isActive() === false) {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        if (!($media->getMediaType() instanceof ImageType)) {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        if (!$this->canProcessFileExtension($media->getFileExtension())) {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        return $this->thumbnailUrlTemplate->getUrl(
            $this->getBaseUrl(),
            $this->getRelativeMediaUrl($media),
            $this->getMaxWidth()
        );
    }

    public function getRelativeMediaUrl(MediaEntity $media): string
    {
        return $this->decoratedService->getRelativeMediaUrl($media);
    }

    public function getAbsoluteThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        if ($this->isActive() === false) {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        if (!$this->canProcessFileExtension($media->getFileExtension())) {
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
        $this->extensionsAllowList = null;
    }

    private function getFallbackUrl(): string
    {
        if ($this->fallbackBaseUrl) {
            return $this->fallbackBaseUrl;
        }

        $this->fallbackBaseUrl = $this->createFallbackUrl();

        return $this->fallbackBaseUrl;
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
            return $this->getFallbackUrl();
        }

        return $this->baseUrl;
    }

    private function canProcessFileExtension(?string $fileExtension): bool
    {
        if ($fileExtension === null) {
            return false;
        }

        $extensionsAllowList = $this->getExtensionsAllowList();

        if (empty($extensionsAllowList)) {
            return false;
        }

        return \in_array(\strtolower($fileExtension), $extensionsAllowList, true);
    }

    /**
     * @return array<string>
     */
    private function getExtensionsAllowList(): array
    {
        if (\is_array($this->extensionsAllowList)) {
            return $this->extensionsAllowList;
        }

        $extensionsAllowListConfig = $this->configReader->getConfig('ExtensionsAllowList');
        $this->extensionsAllowList = [];

        if (\is_string($extensionsAllowListConfig)) {
            $this->extensionsAllowList = \array_unique(
                \array_filter(
                    \explode(
                        ',',
                        (string) \preg_replace('/\s+/', '', \strtolower($extensionsAllowListConfig))
                    )
                )
            );
        }

        return $this->extensionsAllowList;
    }

    private function getMaxWidth(): string
    {
        $maxWidth = $this->configReader->getConfig('ProcessOriginalImageMaxWidth');

        if (\is_string($maxWidth)) {
            return $maxWidth;
        }

        if (\is_int($maxWidth) || \is_float($maxWidth)) {
            return (string) $maxWidth;
        }

        return '3000';
    }

    private function isActive(): bool
    {
        $activeConfig = $this->configReader->getConfig('Active');

        if ($activeConfig === null) {
            return true;
        }

        return $this->testisActive() || !empty($activeConfig);
    }

    private function testisActive(): bool
    {
        $mainRequest = $this->requestStack->getMainRequest();

        if ($mainRequest instanceof Request) {
            return $mainRequest->attributes->has(TestController::REQUEST_ATTRIBUTE_TEST_ACTIVE);
        }

        return false;
    }
}
