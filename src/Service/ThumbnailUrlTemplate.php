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
        $timestamp = $mediaUpdatedAt !== null ? (string) $mediaUpdatedAt->getTimestamp() : '0';

        return str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{mediaUpdatedAt}'],
            [$mediaUrl, $mediaPath, $width, $timestamp],
            $this->getPattern()
        );
    }

    private function getPattern(): string
    {
        if (isset($this->pattern)) {
            return $this->pattern;
        }

        $this->pattern = '{mediaUrl}/{mediaPath}?width={width}';

        $pattern = $this->configReader->getConfig('ThumbnailPattern');

        if (\is_string($pattern) && $pattern !== '') {
            $this->pattern = $pattern;
        }

        return $this->pattern;
    }
}
