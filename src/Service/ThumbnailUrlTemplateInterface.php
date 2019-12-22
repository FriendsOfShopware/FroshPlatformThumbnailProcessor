<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

interface ThumbnailUrlTemplateInterface
{
    public function getUrl($mediaUrl, $mediaPath, $width, $height);
}
