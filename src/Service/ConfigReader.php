<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Frosh\ThumbnailProcessor\Controller\Api\TestController;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ConfigReader
{
    private ?string $salesChannelId = null;

    private bool $salesChannelIdDetected = false;

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly SalesChannelIdDetector $salesChannelIdDetector,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getConfig(string $key): mixed
    {
        $this->detectSalesChannelId();

        $config = $this->systemConfigService->get(\sprintf('FroshPlatformThumbnailProcessor.config.%s', $key), $this->salesChannelId);

        if ($key === 'ProcessOriginalImageMaxWidth') {
            return $this->getProcessOriginalImageMaxWidth($config);
        }

        if ($key === 'Active') {
            return $this->isActive($config);
        }

        return $config ?? null;
    }

    private function detectSalesChannelId(): void
    {
        if ($this->salesChannelIdDetected === true) {
            return;
        }

        $this->salesChannelId = $this->salesChannelIdDetector->getSalesChannelId();
        $this->salesChannelIdDetected = true;
    }

    private function getProcessOriginalImageMaxWidth(mixed $config): string
    {
        if (\is_string($config)) {
            return $config;
        }

        if (\is_int($config) || \is_float($config)) {
            return (string) $config;
        }

        return '3000';
    }

    private function isActive(mixed $config): bool
    {
        if ($config === null) {
            return true;
        }

        return $this->isTestActive() || ($config !== false && $config !== '0' && $config !== 0);
    }

    private function isTestActive(): bool
    {
        $mainRequest = $this->requestStack->getMainRequest();

        if ($mainRequest instanceof Request) {
            return $mainRequest->attributes->has(TestController::REQUEST_ATTRIBUTE_TEST_ACTIVE);
        }

        return false;
    }
}
