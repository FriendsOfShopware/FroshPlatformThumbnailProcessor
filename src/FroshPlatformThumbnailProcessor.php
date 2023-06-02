<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor;

use Frosh\ThumbnailProcessor\DependencyInjection\GeneratorCompilerPass;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\Thumbnail\ThumbnailService;
use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FroshPlatformThumbnailProcessor extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new GeneratorCompilerPass(ThumbnailService::class));
        $container->addCompilerPass(new GeneratorCompilerPass(FileSaver::class));

        parent::build($container);
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }
}
