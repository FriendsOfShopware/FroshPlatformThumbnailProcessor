<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit\Controller\Api;

use Frosh\ThumbnailProcessor\Controller\Api\TestController;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator;
use Shopware\Core\Content\Media\File\FileFetcher;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\Request;
use function PHPUnit\Framework\assertSame;

class TestControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        if (\is_file(TestController::TEST_FILE_PATH . '.bak')) {
            rename(TestController::TEST_FILE_PATH . '.bak', TestController::TEST_FILE_PATH);
        }

        \chmod(TestController::TEST_FILE_PATH, 0644);
    }

    public function testCheckWithoutSalesChannel(): void
    {
        $urlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $mediaRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $fileSaver = $this->createMock(FileSaver::class);
        $fileFetcher = $this->createMock(FileFetcher::class);

        $controller = new TestController(
            $urlGenerator,
            $mediaRepository,
            $mediaFolderRepository,
            $fileSaver,
            $fileFetcher,
        );

        $request = new Request();
        $dataBag = new RequestDataBag();

        $result = $controller->check($request, $dataBag);
        static::assertSame('{"success":false}', $result->getContent());
    }

    public function testCheckFailsWithoutFile(): void
    {
        $urlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $mediaRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $fileSaver = $this->createMock(FileSaver::class);
        $fileFetcher = $this->createMock(FileFetcher::class);

        $controller = new TestController(
            $urlGenerator,
            $mediaRepository,
            $mediaFolderRepository,
            $fileSaver,
            $fileFetcher,
        );

        $request = new Request();
        $dataBag = new RequestDataBag();
        $dataBag->add(['salesChannelId' => null]);

        static::assertFileExists(TestController::TEST_FILE_PATH);
        \rename(TestController::TEST_FILE_PATH, TestController::TEST_FILE_PATH . '.bak');
        static::assertFileDoesNotExist(TestController::TEST_FILE_PATH);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('Test file at "%s" is missing or not readable', \realpath(TestController::TEST_FILE_PATH)));
        $controller->check($request, $dataBag);
    }

    public function testCheckFailsMediaSaving(): void
    {
        $urlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $urlGenerator->expects(static::never())
            ->method('generate');

        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository->expects(static::once())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    1,
                    [['primaryKey' => 'folder-id', 'data' => []]],
                    new Criteria(),
                    Context::createDefaultContext(),
                ),
            );

        $fileSaver = $this->createMock(FileSaver::class);
        $fileFetcher = $this->createMock(FileFetcher::class);

        $controller = new TestController(
            $urlGenerator,
            $mediaRepository,
            $mediaFolderRepository,
            $fileSaver,
            $fileFetcher,
        );

        $request = new Request();
        $dataBag = new RequestDataBag();
        $dataBag->add(['salesChannelId' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Media has not been saved!');
        $controller->check($request, $dataBag);
    }

    public function testCheckFailsProductMediaId(): void
    {
        $urlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $urlGenerator->expects(static::never())
            ->method('generate');

        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository->expects(static::once())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    0,
                    [],
                    new Criteria(),
                    Context::createDefaultContext(),
                ),
            );

        $fileSaver = $this->createMock(FileSaver::class);
        $fileFetcher = $this->createMock(FileFetcher::class);

        $controller = new TestController(
            $urlGenerator,
            $mediaRepository,
            $mediaFolderRepository,
            $fileSaver,
            $fileFetcher,
        );

        $request = new Request();
        $dataBag = new RequestDataBag();
        $dataBag->add(['salesChannelId' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Media folder for product could not have been found!');
        $controller->check($request, $dataBag);
    }

    public function testCheckWithExistingMedia(): void
    {
        $urlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $urlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn(['test' => 'http://localhost/thumbnail.jpg?width=200']);

        $mediaRepository = $this->createMock(EntityRepository::class);
        $mediaRepository->expects(static::once())
            ->method('search')
            ->willReturnCallback(function (Criteria $criteria, Context $context): EntitySearchResult {
                $collection = new EntityCollection();
                $media = new MediaEntity();
                $media->setId('test');
                $media->setFileExtension('jpg');
                $media->setFileName('test.jpg');
                $media->setMimeType('image/jpg');
                $media->setPath('thumbnail.jpg');
                $media->setFileSize(100);
                $media->setCreatedAt(new \DateTime());
                $media->setUpdatedAt(new \DateTime());
                $media->setMediaFolderId('test');
                $media->setMediaType(new ImageType());
                $collection->add($media);

                return new EntitySearchResult(
                    'media_entity',
                    1,
                    $collection,
                    null,
                    new Criteria(),
                    Context::createDefaultContext()
                );
            });

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository->expects(static::never())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    1,
                    [['primaryKey' => 'folder-id', 'data' => []]],
                    new Criteria(),
                    Context::createDefaultContext(),
                ),
            );

        $fileSaver = $this->createMock(FileSaver::class);
        $fileFetcher = $this->createMock(FileFetcher::class);

        $controller = new TestController(
            $urlGenerator,
            $mediaRepository,
            $mediaFolderRepository,
            $fileSaver,
            $fileFetcher,
        );

        $request = new Request();
        $dataBag = new RequestDataBag();
        $dataBag->add(['salesChannelId' => null]);

        $result = $controller->check($request, $dataBag);
        assertSame('{"url":"http:\/\/localhost\/thumbnail.jpg?width=200"}', $result->getContent());
    }

    public function testCheck(): void
    {
        $urlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $urlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn(['test' => 'http://localhost/thumbnail.jpg?width=200']);

        $mediaRepositoryResults = [];
        $mediaRepositoryResults[] = new EntitySearchResult(
            'media_entity',
            0,
            new EntityCollection(),
            null,
            new Criteria(),
            Context::createDefaultContext()
        );

        $collection = new EntityCollection();
        $media = new MediaEntity();
        $media->setId('test');
        $media->setPath('thumbnail.jpg');
        $media->setFileExtension('jpg');
        $media->setFileName('test.jpg');
        $media->setMimeType('image/jpg');
        $media->setFileSize(100);
        $media->setCreatedAt(new \DateTime());
        $media->setUpdatedAt(new \DateTime());
        $media->setMediaFolderId('test');
        $media->setMediaType(new ImageType());
        $collection->add($media);

        $mediaRepositoryResults[] = new EntitySearchResult(
            'media_entity',
            1,
            $collection,
            null,
            new Criteria(),
            Context::createDefaultContext()
        );

        $matcher = static::exactly(2);

        $mediaRepository = $this->createMock(EntityRepository::class);
        $mediaRepository->expects($matcher)
            ->method('search')
            ->willReturnCallback(function () use ($mediaRepositoryResults, $matcher): EntitySearchResult {
                return match ($matcher->numberOfInvocations()) {
                    1 => $mediaRepositoryResults[0],
                    2 => $mediaRepositoryResults[1],
                    default => throw new \RuntimeException('Unexpected invocation count'),
                };
            });

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository->expects(static::once())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    1,
                    [['primaryKey' => 'folder-id', 'data' => []]],
                    new Criteria(),
                    Context::createDefaultContext(),
                ),
            );

        $fileSaver = $this->createMock(FileSaver::class);
        $fileFetcher = $this->createMock(FileFetcher::class);

        $controller = new TestController(
            $urlGenerator,
            $mediaRepository,
            $mediaFolderRepository,
            $fileSaver,
            $fileFetcher,
        );

        $request = new Request();
        $dataBag = new RequestDataBag();
        $dataBag->add(['salesChannelId' => null]);

        $result = $controller->check($request, $dataBag);
        assertSame('{"url":"http:\/\/localhost\/thumbnail.jpg?width=200"}', $result->getContent());
    }

    public function testCheckWithSalesChannel(): void
    {
        $urlGenerator = $this->createMock(AbstractMediaUrlGenerator::class);
        $urlGenerator->expects(static::once())
            ->method('generate')
            ->willReturn(['test' => 'http://localhost/thumbnail.jpg?width=200']);

        $mediaRepositoryResults = [];
        $mediaRepositoryResults[] = new EntitySearchResult(
            'media_entity',
            0,
            new EntityCollection(),
            null,
            new Criteria(),
            Context::createDefaultContext()
        );

        $collection = new EntityCollection();
        $media = new MediaEntity();
        $media->setId('test');
        $media->setFileExtension('jpg');
        $media->setPath('thumbnail.jpg');
        $media->setFileName('test.jpg');
        $media->setMimeType('image/jpg');
        $media->setFileSize(100);
        $media->setCreatedAt(new \DateTime());
        $media->setUpdatedAt(new \DateTime());
        $media->setMediaFolderId('test');
        $media->setMediaType(new ImageType());
        $collection->add($media);

        $mediaRepositoryResults[] = new EntitySearchResult(
            'media_entity',
            1,
            $collection,
            null,
            new Criteria(),
            Context::createDefaultContext()
        );

        $matcher = static::exactly(2);

        $mediaRepository = $this->createMock(EntityRepository::class);
        $mediaRepository->expects($matcher)
            ->method('search')
            ->willReturnCallback(function () use ($mediaRepositoryResults, $matcher): EntitySearchResult {
                return match ($matcher->numberOfInvocations()) {
                    1 => $mediaRepositoryResults[0],
                    2 => $mediaRepositoryResults[1],
                    default => throw new \RuntimeException('Unexpected invocation count'),
                };
            });

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaFolderRepository->expects(static::once())
            ->method('searchIds')
            ->willReturn(
                new IdSearchResult(
                    1,
                    [['primaryKey' => 'folder-id', 'data' => []]],
                    new Criteria(),
                    Context::createDefaultContext(),
                ),
            );

        $fileSaver = $this->createMock(FileSaver::class);
        $fileFetcher = $this->createMock(FileFetcher::class);

        $controller = new TestController(
            $urlGenerator,
            $mediaRepository,
            $mediaFolderRepository,
            $fileSaver,
            $fileFetcher,
        );

        $request = new Request();
        $dataBag = new RequestDataBag();
        $dataBag->add(['salesChannelId' => '1111']);

        $result = $controller->check($request, $dataBag);
        static::assertSame('{"url":"http:\/\/localhost\/thumbnail.jpg?width=200"}', $result->getContent());

        static::assertTrue($request->attributes->has(TestController::REQUEST_ATTRIBUTE_TEST_ACTIVE));
        static::assertSame('1111', $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID));
    }
}
