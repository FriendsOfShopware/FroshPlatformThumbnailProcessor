<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    /** @var string */
    private $pattern;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->pattern = $systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern') ?: '{mediaUrl}/{mediaPath}?width={width}&height={height}';
    }

    public function getUrl($mediaUrl, $mediaPath, $width, $height): string
    {
        $result = $this->pattern;
        $result = str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{height}'],
            [$mediaUrl, $mediaPath, $width, $height],
            $result
        );

        return $result;
    }
}
