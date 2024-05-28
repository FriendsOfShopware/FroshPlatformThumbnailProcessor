<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

interface ThumbnailUrlTemplateInterface
{
    public function getUrl(string $mediaUrl, string $mediaPath, string $width, ?\DateTimeInterface $mediaUpdatedAt): string;
}
