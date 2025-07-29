<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\EventListener;

use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\Aggregate\MediaFolderConfigurationMediaThumbnailSize\MediaFolderConfigurationMediaThumbnailSizeDefinition;
use Shopware\Core\Content\Media\Commands\GenerateThumbnailsCommand;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThumbnailSizesChangedListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly GenerateThumbnailsCommand $generateThumbnailsCommand,
        private readonly EntityRepository $mediaFolderRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MediaFolderConfigurationMediaThumbnailSizeDefinition::ENTITY_NAME . '.written' => 'onThumbnailSizeChanged',
            MediaFolderConfigurationMediaThumbnailSizeDefinition::ENTITY_NAME . '.deleted' => 'onThumbnailSizeChanged',
        ];
    }

    public function onThumbnailSizeChanged(EntityWrittenEvent $event): void
    {
        $updatedMediaFolderConfigurationIds = [];

        foreach ($event->getWriteResults() as $writeResult) {
            $mediaFolderConfigurationId = $writeResult->getPayload()['mediaFolderConfigurationId'] ?? null;

            if ($mediaFolderConfigurationId !== '' && \is_string($mediaFolderConfigurationId)) {
                $updatedMediaFolderConfigurationIds[] = $mediaFolderConfigurationId;
            }
        }

        $updatedMediaFolderConfigurationIds = \array_unique($updatedMediaFolderConfigurationIds);

        if ($updatedMediaFolderConfigurationIds === []) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('configurationId', $updatedMediaFolderConfigurationIds));

        $result = $this->mediaFolderRepository->search($criteria, $event->getContext());

        /** @var MediaFolderEntity $entity */
        foreach ($result->getEntities() as $entity) {
            $parameters = [];

            $parameters['--async'] = null;
            $parameters['--folder-name'] = $entity->getName();

            $this->generateThumbnailsCommand->run(new ArrayInput($parameters, $this->generateThumbnailsCommand->getDefinition()), new NullOutput());
        }
    }
}
