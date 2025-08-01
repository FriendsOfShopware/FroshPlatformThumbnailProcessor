<?php declare(strict_types=1);

namespace Frosh\ThumbnailProcessor\tests;

use Shopware\Core\TestBootstrapper;

$pluginName = 'FroshPlatformThumbnailProcessor';

$paths = [
    '../../../../src/Core/TestBootstrapper.php',
    '../vendor/shopware/core/TestBootstrapper.php',
    '../../../../vendor/shopware/core/TestBootstrapper.php',
];

foreach ($paths as $path) {
    $path = realpath(__DIR__ . '/' . $path);

    if (!\is_string($path)) {
        continue;
    }

    if (!\is_file($path)) {
        continue;
    }

    require $path;

    return (new TestBootstrapper())
        ->setPlatformEmbedded(false)
        ->setLoadEnvFile(true)
        ->setForceInstallPlugins(true)
        ->addActivePlugins($pluginName)
        ->bootstrap()
        ->getClassLoader();
}
