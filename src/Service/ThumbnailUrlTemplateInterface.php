<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

interface ThumbnailUrlTemplateInterface
{
    /**
     * @deprecated parameter height will be removed with next version
     */
    public function getUrl(string $mediaUrl, string $mediaPath, string $width, string $height = ''): string;
}
