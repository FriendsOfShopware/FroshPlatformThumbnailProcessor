<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor;

use Frosh\ThumbnailProcessor\DependencyInjection\ThumbnailServiceGeneratorPass;
use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FroshPlatformThumbnailProcessor extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ThumbnailServiceGeneratorPass());

        parent::build($container);
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }
}
