<?php

namespace Frosh\ThumbnailProcessor\Service;

interface ThumbnailUrlTemplateInterface
{
    public function getUrl($mediaUrl, $mediaPath, $width, $height);
}
