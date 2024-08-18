<?php

declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Storefront\Framework\Twig\Extension;

use Shopware\Storefront\Framework\Twig\Extension\UrlEncodingTwigFilter as ShopwareUrlEncodingTwigFilter;

class UrlEncodingTwigFilter extends ShopwareUrlEncodingTwigFilter
{
    public function encodeUrl(?string $mediaUrl): ?string
    {
        $mediaUrl = parent::encodeUrl($mediaUrl);

        if ($mediaUrl === null) {
            return null;
        }

        // this adds support for imgproxy with the procession options coming with version 3.0
        return \str_replace('%3A', ':', $mediaUrl);
    }
}
