<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ResetInterface;

class UrlGeneratorDecorator implements UrlGeneratorInterface, ResetInterface
{
    private readonly ?string $baseUrl;

    private ?string $fallbackBaseUrl = null;

    private ?array $extensionBlacklist = null;

    public function __construct(
        private readonly UrlGeneratorInterface $decoratedService,
        private readonly ThumbnailUrlTemplateInterface $thumbnailUrlTemplate,
        private readonly RequestStack $requestStack,
        private readonly ConfigReader $configReader,
        ?string $baseUrl = null
    ) {
        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
    }

    public function getAbsoluteMediaUrl(MediaEntity $media): string
    {
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
        $this->extensionBlacklist = null;
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

    private function canProcessFileExtension(?string $fileExtension): bool
    {
        if ($fileExtension === null) {
            return false;
        }

        $extensionBlacklist = $this->getExtensionBlacklist();

        if (empty($extensionBlacklist)) {
            return true;
        }

        return !\in_array(\strtolower($fileExtension), $extensionBlacklist, true);
    }

    private function getExtensionBlacklist(): array
    {
        if (\is_array($this->extensionBlacklist)) {
            return $this->extensionBlacklist;
        }

        $extensionBlacklist = \strtolower((string) $this->configReader->getConfig('ExtensionBlacklist'));

        $this->extensionBlacklist = \array_unique(
            \array_filter(
                \explode(',', \preg_replace('/\s+/', '', $extensionBlacklist))
            )
        );

        return $this->extensionBlacklist;
    }

    private function getMaxWidth(): string
    {
        $maxWidth = (string) $this->configReader->getConfig('ProcessOriginalImageMaxWidth');

        if ($maxWidth !== '') {
            return $maxWidth;
        }

        return '3000';
    }
}
