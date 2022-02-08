<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    /** @var string|null */
    private $pattern;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getUrl(string $mediaUrl, string $mediaPath, string $width, string $height = ''): string
    {
        $segments = \explode('/', $mediaPath);

        foreach ($segments as $index => $segment) {
            $segments[$index] = \rawurlencode($segment);
        }

        $mediaPath = implode('/', $segments);

        return str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}', '{height}'],
            [$mediaUrl, $mediaPath, $width, ''],
            $this->getPattern()
        );
    }

    private function getPattern(): string
    {
        if ($this->pattern) {
            return $this->pattern;
        }

        $pattern = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern');
        if ($pattern && is_string($pattern)) {
            $this->pattern = $pattern;
        } else {
            $this->pattern = '{mediaUrl}/{mediaPath}?width={width}';
        }

        return $this->pattern;
    }
}
