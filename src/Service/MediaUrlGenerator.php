<?php

namespace Frosh\ThumbnailProcessor\Service;

use League\Flysystem\FilesystemOperator;
use Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator;

class MediaUrlGenerator extends AbstractMediaUrlGenerator
{
    /**
     * @var array<string>|null
     */
    private ?array $extensionsAllowList = null;

    public function __construct(
        private readonly AbstractMediaUrlGenerator $decoratedService,
        private readonly ThumbnailUrlTemplateInterface $thumbnailUrlTemplate,
        private readonly FilesystemOperator $filesystem,
        private readonly ConfigReader $configReader
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(array $paths): array
    {
        $originalUrls = $this->decoratedService->generate($paths);

        if ($this->configReader->getConfig('Active') === false) {
            return $originalUrls;
        }

        $baseUrl = $this->getBaseUrl();
        $maxWidth = $this->configReader->getConfig('ProcessOriginalImageMaxWidth');
        \assert(\is_string($maxWidth));

        $urls = [];
        foreach ($paths as $key => $value) {
            if ($this->canRun($value->path) === false) {
                $urls[$key] = $originalUrls[$key];

                continue;
            }

            $urls[$key] = $this->thumbnailUrlTemplate->getUrl(
                $baseUrl,
                $value->path,
                $maxWidth
            );
        }

        return $urls;
    }

    private function canRun(string $path): bool
    {
        $fileExtension = \pathinfo($path, \PATHINFO_EXTENSION);

        if (!\is_string($fileExtension)) {
            return false;
        }

        if (!$this->canProcessFileExtension($fileExtension)) {
            return false;
        }

        return true;
    }

    private function canProcessFileExtension(string $fileExtension): bool
    {
        $extensionsAllowList = $this->getExtensionsAllowList();

        if (empty($extensionsAllowList)) {
            return false;
        }

        return \in_array(\strtolower($fileExtension), $extensionsAllowList, true);
    }

    private function getBaseUrl(): string
    {
        return $this->filesystem->publicUrl('');
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
