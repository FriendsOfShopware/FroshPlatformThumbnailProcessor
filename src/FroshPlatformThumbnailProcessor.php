<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor;

use Composer\Autoload\ClassLoader;
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

        $file = __DIR__ . '/../vendor/autoload.php';

        if (!is_file($file)) {
            return;
        }

        $classLoader = require_once $file;

        if ($classLoader instanceof ClassLoader) {
            $classLoader->unregister();
            $classLoader->register(false);
        }
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }
}
