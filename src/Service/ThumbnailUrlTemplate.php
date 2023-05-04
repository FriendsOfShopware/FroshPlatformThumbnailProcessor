<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    private ?string $pattern = null;

    private ConfigReader $configReader;

    public function __construct(
        ConfigReader $configReader
    ) {
        $this->configReader = $configReader;
    }

    public function getUrl(string $mediaUrl, string $mediaPath, string $width, string $height = ''): string
    {
        return str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{height}'],
            [$mediaUrl, $mediaPath, $width, ''],
            $this->getPattern()
        );
    }

    private function getPattern(): string
    {
        if ($this->pattern) {
            return $this->pattern;
        }

        $pattern = $this->configReader->getConfig('ThumbnailPattern');
        $this->pattern = $pattern && \is_string($pattern) ? $pattern : '{mediaUrl}/{mediaPath}?width={width}';

        return $this->pattern;
    }
}
