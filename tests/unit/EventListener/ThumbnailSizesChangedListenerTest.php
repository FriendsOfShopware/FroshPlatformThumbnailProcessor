<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\EventListener;

use Frosh\ThumbnailProcessor\EventListener\ThumbnailSizesChangedListener;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderCollection;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\Aggregate\MediaFolderConfigurationMediaThumbnailSize\MediaFolderConfigurationMediaThumbnailSizeDefinition;
use Shopware\Core\Content\Media\Commands\GenerateThumbnailsCommand;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use Symfony\Component\Messenger\MessageBus;

class ThumbnailSizesChangedListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        static::assertEquals([
            MediaFolderConfigurationMediaThumbnailSizeDefinition::ENTITY_NAME . '.written' => 'onThumbnailSizeChanged',
            MediaFolderConfigurationMediaThumbnailSizeDefinition::ENTITY_NAME . '.deleted' => 'onThumbnailSizeChanged',
        ], ThumbnailSizesChangedListener::getSubscribedEvents());
    }

    public function testOnThumbnailSizeChanged(): void
    {
        $generateThumbnailsCommand = $this->createMock(GenerateThumbnailsCommand::class);
        $generateThumbnailsCommand->expects(static::once())
            ->method('run');

        $generateThumbnailsCommand->expects(static::once())
            ->method('getDefinition')
            ->willReturn((new GenerateThumbnailsCommand(
                $this->createMock(ThumbnailService::class),
                $this->createMock(EntityRepository::class),
                $this->createMock(EntityRepository::class),
                $this->createMock(MessageBus::class),
            ))->getDefinition());

        $mediaFolderEntity = new MediaFolderEntity();
        $mediaFolderEntity->setId('1111');
        $mediaFolderEntity->setName('Product Media');

        $mediaFolderCollection = new MediaFolderCollection();
        $mediaFolderCollection->add($mediaFolderEntity);

        /** @var StaticEntityRepository<MediaFolderCollection> */
        $mediaFolderRepository = new StaticEntityRepository([$mediaFolderCollection]);

        $thumbnailSizesChangedListener = new ThumbnailSizesChangedListener(
            $generateThumbnailsCommand,
            $mediaFolderRepository
        );

        $entityName = MediaFolderConfigurationMediaThumbnailSizeDefinition::ENTITY_NAME;

        $writtenResult = new EntityWriteResult('1111', ['mediaFolderConfigurationId' => '1111'], $entityName, EntityWriteResult::OPERATION_INSERT);
        $writtenEvent = new EntityWrittenEvent($entityName, [$writtenResult], Context::createDefaultContext());

        $thumbnailSizesChangedListener->onThumbnailSizeChanged($writtenEvent);
    }

    public function testOnThumbnailSizeChangedWithEmptyPayload(): void
    {
        $generateThumbnailsCommand = $this->createMock(GenerateThumbnailsCommand::class);
        $generateThumbnailsCommand->expects(static::never())
            ->method('run');

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository->expects(static::never())
            ->method('search');

        $thumbnailSizesChangedListener = new ThumbnailSizesChangedListener(
            $generateThumbnailsCommand,
            $mediaFolderRepository
        );

        $entityName = MediaFolderConfigurationMediaThumbnailSizeDefinition::ENTITY_NAME;

        $writtenResult = new EntityWriteResult('1111', [], $entityName, EntityWriteResult::OPERATION_INSERT);
        $writtenEvent = new EntityWrittenEvent($entityName, [$writtenResult], Context::createDefaultContext());

        $thumbnailSizesChangedListener->onThumbnailSizeChanged($writtenEvent);
    }
}
