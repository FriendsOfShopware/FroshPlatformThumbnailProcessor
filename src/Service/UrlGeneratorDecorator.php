<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use League\Flysystem\FilesystemOperator;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Symfony\Contracts\Service\ResetInterface;

class UrlGeneratorDecorator implements UrlGeneratorInterface, ResetInterface
{
    /**
     * @var array<string>|null
     */
    private ?array $extensionsAllowList = null;

    public function __construct(
        private readonly UrlGeneratorInterface $decoratedService,
        private readonly ThumbnailUrlTemplateInterface $thumbnailUrlTemplate,
        private readonly FilesystemOperator $filesystem,
        private readonly ConfigReader $configReader
    ) {
    }

    public function getAbsoluteMediaUrl(MediaEntity $media): string
    {
        if ($this->canRun($media) === false) {
            return $this->decoratedService->getAbsoluteMediaUrl($media);
        }

        $maxWidth = $this->configReader->getConfig('ProcessOriginalImageMaxWidth');
        \assert(\is_string($maxWidth));

        return $this->thumbnailUrlTemplate->getUrl(
            $this->getBaseUrl(),
            $this->getRelativeMediaUrl($media),
            $maxWidth
        );
    }

    public function getRelativeMediaUrl(MediaEntity $media): string
    {
        return $this->decoratedService->getRelativeMediaUrl($media);
    }

    public function getAbsoluteThumbnailUrl(MediaEntity $media, MediaThumbnailEntity $thumbnail): string
    {
        if ($this->canRun($media) === false) {
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
        $this->extensionsAllowList = null;
    }

    private function canRun(MediaEntity $media): bool
    {
        if ($this->configReader->getConfig('Active') === false) {
            return false;
        }

        if (!$this->canProcessFileExtension($media->getFileExtension())) {
            return false;
        }

        return true;
    }

    private function getBaseUrl(): string
    {
        return \rtrim($this->filesystem->publicUrl(''), '/');
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
}
