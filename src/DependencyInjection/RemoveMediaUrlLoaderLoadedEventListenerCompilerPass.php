<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\DependencyInjection;

use Shopware\Core\Content\Media\Core\Application\MediaUrlLoader;
use Shopware\Core\Framework\DependencyInjection\CompilerPass\RemoveEventListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveMediaUrlLoaderLoadedEventListenerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        RemoveEventListener::remove($container, MediaUrlLoader::class, [
            ['media.loaded', 'loaded'],
            ['media.partial_loaded', 'loaded'],
        ]);
    }
}
