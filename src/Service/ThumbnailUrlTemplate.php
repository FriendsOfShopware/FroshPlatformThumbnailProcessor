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

    public function __construct(
        SystemConfigService $systemConfigService,
        RequestStack $requestStack
    ) {
        $this->requestStack = $requestStack;
        $this->systemConfigService = $systemConfigService;
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

        $pattern = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern', $this->getSalesChannelId());
        $this->pattern = $pattern && \is_string($pattern) ? $pattern : '{mediaUrl}/{mediaPath}?width={width}';

        return $this->pattern;
    }

    private function getSalesChannelId(): ?string
    {
        if (!$this->requestStack === null) {
            return null;
        }

        if ($this->requestStack->getMainRequest() === null) {
            return null;
        }

        return $this->requestStack->getMainRequest()->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
    }
}
