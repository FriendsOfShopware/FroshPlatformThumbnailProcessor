<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    /** @var string */
    private $pattern;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $patternConfig = $systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern');

        if ($patternConfig && is_string($patternConfig)) {
            $this->pattern = $patternConfig;
        } else {
            $this->pattern = '{mediaUrl}/{mediaPath}?width={width}';
        }
    }

    public function getUrl(string $mediaUrl, string $mediaPath, string $width, string $height): string
    {
        $result = $this->pattern;
        return str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{height}'],
            [$mediaUrl, $mediaPath, $width, $height],
            $result
        );
    }
}
