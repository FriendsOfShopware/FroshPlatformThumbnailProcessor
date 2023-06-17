<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Service;

use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesChannelIdDetector
{
    private EntityRepository $productExportRepository;
    private RequestStack $requestStack;

    public function __construct(
        RequestStack $requestStack,
        EntityRepository $productExportRepository
    ) {
        $this->requestStack = $requestStack;
        $this->productExportRepository = $productExportRepository;
    }

    public function getSalesChannelId(): ?string
    {
        $mainRequest = $this->requestStack->getMainRequest();

        if ($mainRequest === null) {
            return null;
        }

        $salesChannelId = $mainRequest->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        if (\is_string($salesChannelId)) {
            return $salesChannelId;
        }

        if ($mainRequest->attributes->get('_route') === 'store-api.product.export') {
            return $this->getSalesChannelIdByProductExport($mainRequest);
        }

        return null;
    }

    private function getSalesChannelIdByProductExport(Request $mainRequest): ?string
    {
        $fileName = $mainRequest->get('fileName');
        $accessKey = $mainRequest->get('accessKey');

        if (!\is_string($fileName) || !\is_string($accessKey)) {
            return null;
        }

        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('fileName', $fileName))
            ->addFilter(new EqualsFilter('accessKey', $accessKey))
            ->addFilter(new EqualsFilter('salesChannel.active', true));

        /** @var ProductExportEntity|null $productExport */
        $productExport = $this->productExportRepository->search($criteria, Context::createDefaultContext())->first();

        return $productExport?->getSalesChannelId();
    }
}
