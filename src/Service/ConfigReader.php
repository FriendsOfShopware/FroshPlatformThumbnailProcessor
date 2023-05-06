<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigReader
{
    /**
     * @var array<mixed>|null
     */
    private ?array $config = null;

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly SalesChannelIdDetector $salesChannelIdDetector
    ) {
    }

    public function getConfig(string $key): mixed
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
