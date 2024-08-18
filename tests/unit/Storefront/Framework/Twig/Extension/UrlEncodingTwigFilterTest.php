<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Storefront\Framework\Twig\Extension;

use Frosh\ThumbnailProcessor\Storefront\Framework\Twig\Extension\UrlEncodingTwigFilter;
use PHPUnit\Framework\TestCase;

class UrlEncodingTwigFilterTest extends TestCase
{
    public function testEncodeUrl(): void
    {
        $urlEncodingTwigFilter = new UrlEncodingTwigFilter();
        $result = $urlEncodingTwigFilter->encodeUrl('https://example.com/w:1/image.jpg');
        static::assertSame('https://example.com/w:1/image.jpg', $result);
    }

    public function testEncodeUrlNull(): void
    {
        $urlEncodingTwigFilter = new UrlEncodingTwigFilter();
        $result = $urlEncodingTwigFilter->encodeUrl(null);
        static::assertNull($result);
    }
}