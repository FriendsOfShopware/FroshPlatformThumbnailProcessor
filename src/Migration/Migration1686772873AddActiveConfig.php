<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1686772873AddActiveConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1686772873;
    }

    public function update(Connection $connection): void
    {
        $currentPluginVersion = $this->getPluginVersion($connection);

        if (empty($currentPluginVersion)) {
            return;
        }

        // we added the active flag with version 2.0.2, so we don't need to set the default value afterward
        if (\version_compare($currentPluginVersion, '2.0.2', '>')) {
            return;
        }

        $connection->update(
            'system_config',
            [
                'configuration_value' => '{"_value": true}',
            ],
            ['configuration_key' => 'FroshPlatformThumbnailProcessor.config.Active']
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function getPluginVersion(Connection $connection): ?string
    {
        $builder = $connection->createQueryBuilder()->select('`version`')
            ->from('plugin')
            ->where('`name` = :pluginName')
            ->andWhere('`active` = 1')
            ->setParameter('pluginName', 'FroshPlatformThumbnailProcessor');

        $result = $builder->execute()->fetchOne();

        if (\is_string($result)) {
            return $result;
        }

        return null;
    }
}
