<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Tests\Unit;

use Frosh\ThumbnailProcessor\FroshPlatformThumbnailProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FroshPlatformThumbnailProcessorTest extends TestCase
{
    public function testBuild(): void
    {
        $pluginBootstrap = new FroshPlatformThumbnailProcessor(true, __DIR__ . '/../../');

        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(static::exactly(3))
            ->method('addCompilerPass');

        $pluginBootstrap->build($container);
    }

    public function testExecuteComposerCommands(): void
    {
        $pluginBootstrap = new FroshPlatformThumbnailProcessor(true, __DIR__ . '/../../');
        static::assertTrue($pluginBootstrap->executeComposerCommands());
    }
}
