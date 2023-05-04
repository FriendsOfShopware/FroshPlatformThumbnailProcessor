<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    private ?string $pattern = null;

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getUrl(string $mediaUrl, string $mediaPath, string $width): string
    {
        return str_replace(
            ['{mediaUrl}', '{mediaPath}', '{width}'],
            [$mediaUrl, $mediaPath, $width],
            $this->getPattern()
        );
    }

    private function getPattern(): string
    {
        if ($this->pattern) {
            return $this->pattern;
        }

        $salesChannelId = $this->requestStack?->getMainRequest()?->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        $pattern = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern', $salesChannelId);
        $this->pattern = $pattern && \is_string($pattern) ? $pattern : '{mediaUrl}/{mediaPath}?width={width}';

        return $this->pattern;
    }
}
