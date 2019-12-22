<?php

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

    /**
     * @param string $mediaUrl
     * @param string $mediaPath
     * @param string $width
     * @param string $height
     *
     * @return string
     */
    public function getUrl($mediaUrl, $mediaPath, $width, $height)
    {

        $result = $this->pattern;
        $result = str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{height}'],
            [$mediaUrl, $mediaPath, $width, $height], $result);
        return $result;
    }
}
