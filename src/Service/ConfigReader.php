<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigReader
{
    /**
     * @var array<mixed>|null
     */
    private ?array $config = null;

    private SystemConfigService $systemConfigService;

    private SalesChannelIdDetector $salesChannelIdDetector;

    public function __construct(
        SystemConfigService $systemConfigService,
        SalesChannelIdDetector $salesChannelIdDetector
    ) {
        $this->salesChannelIdDetector = $salesChannelIdDetector;
        $this->systemConfigService = $systemConfigService;
    }

    public function getConfig(string $key)
    {
        if (!$this->config) {
            $salesChannelId = $this->salesChannelIdDetector->getSalesChannelId();
            $config = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config', $salesChannelId);

            if (!\is_array($config)) {
                return null;
            }

            $this->config = $config;
        }

        return $this->config[$key] ?? null;
    }
}
