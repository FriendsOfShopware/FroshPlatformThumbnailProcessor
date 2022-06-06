<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Storefront\Framework\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UrlEncodingTwigFilter extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('frosh_encode_url', [$this, 'encodeUrl']),
        ];
    }

    public function encodeUrl(?string $mediaUrl): ?string
    {
        if ($mediaUrl === null) {
            return null;
        }

        $urlInfo = parse_url($mediaUrl);

        // we encode just parts after "/media/" to add support for imgproxy and paths which always need to be encoded
        $paths = \explode('/media/', $urlInfo['path']);
        $paths[0] .= '/media';

        $relativeImagePath = $paths[1];

        $relativeImagePathSegments = explode('/', $relativeImagePath);
        foreach ($relativeImagePathSegments as $index => $segment) {
            $relativeImagePathSegments[$index] = \rawurlencode($segment);
        }

        $paths[1] = implode('/', $relativeImagePathSegments);

        $path = implode('/', $paths);
        if (isset($urlInfo['query'])) {
            $path .= "?{$urlInfo['query']}";
        }

        $encodedPath = '';

        if (isset($urlInfo['scheme'])) {
            $encodedPath = "{$urlInfo['scheme']}://";
        }

        if (isset($urlInfo['host'])) {
            $encodedPath .= "{$urlInfo['host']}";
        }

        if (isset($urlInfo['port'])) {
            $encodedPath .= ":{$urlInfo['port']}";
        }

        return $encodedPath . $path;
    }
}
