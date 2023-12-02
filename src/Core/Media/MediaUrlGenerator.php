<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Core\Media;

use Frosh\ThumbnailProcessor\Service\ConfigReader;
use Frosh\ThumbnailProcessor\Service\ThumbnailUrlTemplateInterface;
use League\Flysystem\FilesystemOperator;
use Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator;
use Shopware\Core\Content\Media\Core\Params\UrlParams;

class MediaUrlGenerator extends AbstractMediaUrlGenerator
{
    /**
     * @var array<string>|null
     */
    private ?array $extensionsAllowList = null;

    public function __construct(
        private readonly AbstractMediaUrlGenerator $mediaUrlGenerator,
        private readonly ThumbnailUrlTemplateInterface $thumbnailUrlTemplate,
        private readonly FilesystemOperator $filesystem,
        private readonly ConfigReader $configReader
    ) {
    }

    /**
     * @param array<string|int, UrlParams> $paths indexed by id, value contains the path
     *
     * @return array<string|int, string> indexed by id, value contains the absolute url (e.g. https://my.shop.de/media/0a/test.jpg)
     */
    public function generate(array $paths): array
    {
        $originalUrls = $this->mediaUrlGenerator->generate($paths);

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
                $this->getWidth($maxWidth, $value)
            );
        }

        return $urls;
    }

    private function canRun(string $path): bool
    {
        $fileExtension = \pathinfo($path, \PATHINFO_EXTENSION);
        \assert(\is_string($fileExtension));

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
        return \rtrim($this->filesystem->publicUrl(''), '/');
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

    private function getWidth(string $maxWidth, UrlParams $value): string
    {
        $width = $maxWidth;

        if ($value instanceof ExtendedUrlParams) {
            return $value->width ? (string) $value->width : $maxWidth;
        }

        return $width;
    }
}
