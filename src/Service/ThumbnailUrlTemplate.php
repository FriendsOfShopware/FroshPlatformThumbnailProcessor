<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    private ?string $pattern = null;

    private SystemConfigService $systemConfigService;

    private RequestStack $requestStack;

    public function __construct(SystemConfigService $systemConfigService, RequestStack $requestStack)
    {
        $this->systemConfigService = $systemConfigService;
        $this->requestStack = $requestStack;
    }

    public function getUrl(string $mediaUrl, string $mediaPath, string $width, string $height = ''): string
    {
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

        $salesChannelId = null;

        $masterRequest = $this->requestStack->getMainRequest();
        if ($masterRequest) {
            $salesChannelId = $masterRequest->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
        }

        $pattern = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern', $salesChannelId);
        if ($pattern && is_string($pattern)) {
            $this->pattern = $pattern;
        } else {
            $this->pattern = '{mediaUrl}/{mediaPath}?width={width}';
        }

        return $this->pattern;
    }
}
