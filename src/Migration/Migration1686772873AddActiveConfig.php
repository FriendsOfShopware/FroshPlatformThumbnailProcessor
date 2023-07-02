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

        // we added the active flag with version 2.1.0 and 3.0.3, so we don't need to set the default value afterward
        if (!$this->needUpdate($currentPluginVersion)) {
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

    private function needUpdate(string $currentPluginVersion): bool
    {
        // we added the active flag with version 3.0.3, so we don't need to set the default value afterward
        if (\version_compare($currentPluginVersion, '3.0.3', '>')) {
            return false;
        }

        // we added the active flag with version 2.1.0 (other branch), so we don't need to set the default value afterward
        if (\version_compare($currentPluginVersion, '3.0.0', '<')
            && \version_compare($currentPluginVersion, '2.1.0', '>=')) {
            return false;
        }

        return true;
    }

    private function getPluginVersion(Connection $connection): ?string
    {
        $builder = $connection->createQueryBuilder()->select('`version`')
            ->from('plugin')
            ->where('`name` = :pluginName')
            ->andWhere('`active` = 1')
            ->setParameter('pluginName', 'FroshPlatformThumbnailProcessor');

        $result = $builder->executeQuery()->fetchOne();

        if (\is_string($result)) {
            return $result;
        }

        return null;
    }
}
