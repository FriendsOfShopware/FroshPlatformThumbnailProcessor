<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;

class ThumbnailUrlTemplate implements ThumbnailUrlTemplateInterface
{
    private ?string $pattern = null;

    private SystemConfigService $systemConfigService;

    private RequestStack $requestStack;

    private EntityRepositoryInterface $productExportRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        RequestStack $requestStack,
        EntityRepositoryInterface $productExportRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->requestStack = $requestStack;
        $this->productExportRepository = $productExportRepository;
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

        $salesChannelId = $this->getSalesChannelId();

        $pattern = $this->systemConfigService->get('FroshPlatformThumbnailProcessor.config.ThumbnailPattern', $salesChannelId);
        if ($pattern && \is_string($pattern)) {
            $this->pattern = $pattern;
        } else {
            $this->pattern = '{mediaUrl}/{mediaPath}?width={width}';
        }

        return $this->pattern;
    }

    private function getSalesChannelId(): ?string
    {
        $masterRequest = $this->requestStack->getMainRequest();
        if (!$masterRequest) {
            return null;
        }

        $salesChannelId = $masterRequest->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
        if ($salesChannelId) {
            return $salesChannelId;
        }

        if ($masterRequest->attributes->get('_route') === 'store-api.product.export') {
            $fileName = $masterRequest->attributes->get('fileName');
            $accessKey = $masterRequest->attributes->get('accessKey');

            $criteria = new Criteria();
            $criteria
                ->addFilter(new EqualsFilter('fileName', $fileName))
                ->addFilter(new EqualsFilter('accessKey', $accessKey))
                ->addFilter(new EqualsFilter('salesChannel.active', true));

            /** @var ProductExportEntity|null $productExport */
            $productExport = $this->productExportRepository->search($criteria, Context::createDefaultContext())->first();

            return $productExport ? $productExport->getSalesChannelId() : null;
        }

        return null;
    }
}
