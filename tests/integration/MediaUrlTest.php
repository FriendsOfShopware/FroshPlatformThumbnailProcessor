<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Integration;

use Frosh\ThumbnailProcessor\Controller\Api\TestController;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Commands\GenerateThumbnailsCommand;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
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

    private UrlGeneratorInterface $urlGenerator;

    private EntityRepository $mediaRepository;

    private GenerateThumbnailsCommand $generateThumbnailsCommand;

    private SystemConfigService $systemConfigService;

    private Context $context;

    protected function setUp(): void
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();

        $this->urlGenerator = $container->get(UrlGeneratorInterface::class);
        \assert($this->urlGenerator instanceof UrlGeneratorInterface);

        $this->mediaRepository = $container->get('media.repository');
        \assert($this->mediaRepository instanceof EntityRepository);

        $this->generateThumbnailsCommand = $container->get(GenerateThumbnailsCommand::class);
        \assert($this->generateThumbnailsCommand instanceof GenerateThumbnailsCommand);

        $this->systemConfigService = $container->get(SystemConfigService::class);
        \assert($this->systemConfigService instanceof SystemConfigService);

        $this->context = Context::createDefaultContext();
    }

    public function testMediaUrlWithInactiveConfig(): void
    {
        $fixture = $this->mediaFixtures['NamedMimePngEtxPngWithFolder'];
        $media = $this->getPngWithFolder();

        static::assertStringEndsWith('pngFileWithExtensionAndFolder.png', $media->getUrl());

        $folderName = null;
        if (\is_array($fixture['mediaFolder']) && !empty($fixture['mediaFolder']['name'])) {
            $folderName = $fixture['mediaFolder']['name'];
        }

        static::assertNotEmpty($folderName);

        $resource = fopen(TestController::TEST_FILE_PATH, 'rb');
        \assert($resource !== false);

        $filePath = $this->urlGenerator->getRelativeMediaUrl($media);
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
            $thumbnailUrl = $this->urlGenerator->getAbsoluteThumbnailUrl(
                $media,
                $thumbnail
            );

            static::assertStringStartsWith('http://localhost:8000', $thumbnailUrl);
            static::assertStringNotContainsString('thumbnail/', $thumbnailUrl);
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
        if (\is_array($fixture['mediaFolder']) && !empty($fixture['mediaFolder']['name'])) {
            $folderName = $fixture['mediaFolder']['name'];
        }

        static::assertNotEmpty($folderName);

        $resource = fopen(TestController::TEST_FILE_PATH, 'rb');
        \assert($resource !== false);

        $filePath = $this->urlGenerator->getRelativeMediaUrl($media);
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
            $thumbnailUrl = $this->urlGenerator->getRelativeThumbnailUrl(
                $media,
                $thumbnail
            );

            static::assertStringStartsWith('http://localhost:8000', $thumbnailUrl);
            static::assertStringEndsWith('pngFileWithExtensionAndFolder.png?width=' . $thumbnail->getWidth(), $thumbnailUrl);
            static::assertStringNotContainsString('thumbnail/', $thumbnailUrl);

            static::assertFalse($fileSystem->has(\str_replace('media/', 'thumbnail/', $filePath)));
            static::assertFalse($fileSystem->has($thumbnailUrl));
        }
    }
}
