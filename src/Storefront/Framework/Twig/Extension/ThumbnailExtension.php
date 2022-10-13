<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Storefront\Framework\Twig\Extension;

use Frosh\ThumbnailProcessor\Storefront\Framework\Twig\ThumbProcThumbnailTokenParser;
use Shopware\Core\Framework\Adapter\Twig\TemplateFinder;
use Twig\Extension\AbstractExtension;

class ThumbnailExtension extends AbstractExtension
{
    private TemplateFinder $finder;

    /**
     * @internal
     */
    public function __construct(TemplateFinder $finder)
    {
        $this->finder = $finder;
    }

    public function getTokenParsers(): array
    {
        return [
            new ThumbProcThumbnailTokenParser(),
        ];
    }

    public function getFinder(): TemplateFinder
    {
        return $this->finder;
    }
}
