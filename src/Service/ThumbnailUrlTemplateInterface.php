<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

interface ThumbnailUrlTemplateInterface
{
    /**
     * @param string $mediaUrl
     * @param string $mediaPath
     * @param string $width
     * @param string $height
     */
    public function getUrl($mediaUrl, $mediaPath, $width, $height): string;
}
