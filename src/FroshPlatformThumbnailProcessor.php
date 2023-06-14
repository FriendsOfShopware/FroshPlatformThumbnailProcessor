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
        $builder = $container->get('Doctrine\DBAL\Connection')->createQueryBuilder()->select('`active`')
            ->from('plugin')
            ->where('`name` = :pluginName')
            ->andWhere('`active` = 1')
            ->setParameter('pluginName', 'FroshPlatformThumbnailProcessor');

        $active = $builder->executeQuery()->fetchOne();

        if ($active === null || !empty($active)) {
            $container->addCompilerPass(new GeneratorCompilerPass(ThumbnailService::class));
            $container->addCompilerPass(new GeneratorCompilerPass(FileSaver::class));
        }

        parent::build($container);
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }
}
