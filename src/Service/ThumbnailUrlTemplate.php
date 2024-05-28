<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    private ?string $pattern = null;

    public function __construct(
        private readonly ConfigReader $configReader
    ) {
    }

    public function getUrl(string $mediaUrl, string $mediaPath, string $width, ?\DateTimeInterface $mediaUpdatedAt): string
    {
        return str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{mediaUpdatedAt}'],
            [$mediaUrl, $mediaPath, $width, $mediaUpdatedAt?->getTimestamp() ?: 'null'],
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
