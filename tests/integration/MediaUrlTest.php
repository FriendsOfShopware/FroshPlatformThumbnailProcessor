<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Integration;

use Frosh\ThumbnailProcessor\Controller\Api\TestController;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Commands\GenerateThumbnailsCommand;
use Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Test\Media\MediaFixtures;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\QueueTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MediaUrlTest extends TestCase
{
    use IntegrationTestBehaviour;
    use MediaFixtures;
    use QueueTestBehaviour;

    private EntityRepository $mediaRepository;

    private GenerateThumbnailsCommand $generateThumbnailsCommand;

    private SystemConfigService $systemConfigService;

    private Context $context;

    protected function setUp(): void
    {
        $container = $this::getContainer();

        $urlGenerator = $container->get(AbstractMediaUrlGenerator::class);

        $this->mediaRepository = $container->get('media.repository');

        $this->generateThumbnailsCommand = $container->get(GenerateThumbnailsCommand::class);

        $this->systemConfigService = $container->get(SystemConfigService::class);

        $this->context = Context::createDefaultContext();
    }

    public function testMediaUrlWithInactiveConfigResultsInOriginalMedia(): void
    {
        $fixture = $this->mediaFixtures['NamedMimePngEtxPngWithFolder'];
        $media = $this->getPngWithFolder();

        static::assertMatchesRegularExpression('/http:\/\/localhost:8000\/media\/_test\/pngFileWithExtensionAndFolder\.png\?(\d+|ts=\d+)/', $media->getUrl());

        $folderName = null;

        if (\is_array($fixture['mediaFolder'])) {
            $name = $fixture['mediaFolder']['name'] ?? null;
            if ($name !== null && $name !== '') {
                $folderName = $name;
            }
        }

        static::assertNotEmpty($folderName);

        $resource = fopen(TestController::TEST_FILE_PATH, 'rb');
        \assert($resource !== false);

        $filePath = $media->getPath();
        $fileSystem = $this->getPublicFilesystem();
        $fileSystem->writeStream($filePath, $resource);

        static::assertTrue($fileSystem->has($filePath));

        $parameters = [];
        $parameters['--folder-name'] = $folderName;

        $this->generateThumbnailsCommand->run(new ArrayInput($parameters, $this->generateThumbnailsCommand->getDefinition()), new NullOutput());

        $this->runWorker();

        $searchCriteria = new Criteria();
        $searchCriteria->setLimit(1);
        $searchCriteria->addFilter(new EqualsFilter('media.id', $media->getId()));
        $searchCriteria->addAssociation('mediaFolder.configuration.mediaThumbnailSizes');

        $mediaResult = $this->mediaRepository->search($searchCriteria, $this->context);

        /** @var MediaEntity $updatedMedia */
        $updatedMedia = $mediaResult->getEntities()->first();

        $thumbnails = $updatedMedia->getThumbnails();
        static::assertInstanceOf(MediaThumbnailCollection::class, $thumbnails);
        static::assertEquals(2, $thumbnails->count());

        foreach ($thumbnails as $thumbnail) {
            $thumbnailUrl = $thumbnail->getUrl();

            static::assertStringStartsWith('http://localhost:8000', $thumbnailUrl);
            static::assertStringNotContainsString('thumbnail/', $thumbnailUrl);
            static::assertSame($media->getUrl(), $thumbnailUrl);
            static::assertFalse($fileSystem->has(\str_replace('media/', 'thumbnail/', $filePath)));
            static::assertFalse($fileSystem->has($thumbnailUrl));
        }
    }

    public function testMediaUrlWithActiveConfig(): void
    {
        $this->systemConfigService->set('FroshPlatformThumbnailProcessor.config.Active', true);

        $fixture = $this->mediaFixtures['NamedMimePngEtxPngWithFolder'];
        $media = $this->getPngWithFolder();

        static::assertStringEndsWith('pngFileWithExtensionAndFolder.png?width=3000', $media->getUrl());

        $folderName = null;
        if (\is_array($fixture['mediaFolder'])) {
            $name = $fixture['mediaFolder']['name'] ?? null;
            if ($name !== null && $name !== '') {
                $folderName = $name;
            }
        }

        static::assertNotEmpty($folderName);

        $resource = fopen(TestController::TEST_FILE_PATH, 'rb');
        \assert($resource !== false);

        $filePath = $media->getPath();
        $fileSystem = $this->getPublicFilesystem();
        $fileSystem->writeStream($filePath, $resource);

        static::assertTrue($fileSystem->has($filePath));

        $parameters = [];
        $parameters['--folder-name'] = $folderName;

        $this->generateThumbnailsCommand->run(new ArrayInput($parameters, $this->generateThumbnailsCommand->getDefinition()), new NullOutput());

        $this->runWorker();

        $searchCriteria = new Criteria();
        $searchCriteria->setLimit(1);
        $searchCriteria->addFilter(new EqualsFilter('media.id', $media->getId()));
        $searchCriteria->addAssociation('mediaFolder.configuration.mediaThumbnailSizes');

        $mediaResult = $this->mediaRepository->search($searchCriteria, $this->context);

        /** @var MediaEntity $updatedMedia */
        $updatedMedia = $mediaResult->getEntities()->first();

        $thumbnails = $updatedMedia->getThumbnails();
        static::assertInstanceOf(MediaThumbnailCollection::class, $thumbnails);
        static::assertEquals(2, $thumbnails->count());

        foreach ($thumbnails as $thumbnail) {
            $thumbnailUrl = $thumbnail->getUrl();

            static::assertStringStartsWith('http://localhost:8000', $thumbnailUrl);
            static::assertStringEndsWith('pngFileWithExtensionAndFolder.png?width=' . $thumbnail->getWidth(), $thumbnailUrl);
            static::assertStringNotContainsString('thumbnail/', $thumbnailUrl);

            static::assertFalse($fileSystem->has(\str_replace('media/', 'thumbnail/', $filePath)));
            static::assertFalse($fileSystem->has($thumbnailUrl));
        }
    }
}
