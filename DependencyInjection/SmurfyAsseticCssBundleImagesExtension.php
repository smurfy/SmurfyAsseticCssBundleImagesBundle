<?php
/*
 * This file is part of the SmurfyAsseticCssBundleImagesBundle package.
 *
 * (c) smurfy <https://github.com/smurfy>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Smurfy\AsseticCssBundleImagesBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\FileLocator;

/**
 * This class contains the mapping between the configuration and the DI container
 */
class SmurfyAsseticCssBundleImagesExtension extends Extension
{
    /**
     * Loads all configs and adds the relevant ones to the DI container
     * Also loads the DI Services
     *
     * @param array            $configs   Array of Configs
     * @param ContainerBuilder $container the DI Container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['filters'])) {
            $config['filters'] = array();
        }

        $container->setParameter('smurfy.assetic.output', $config['output']);
        $container->setParameter('smurfy.assetic.absolute', $config['absolute']);
        $container->setParameter('smurfy.assetic.filters', $config['filters']);
        $container->setParameter('smurfy.assetic.less_url_rewrite_workaround', $config['lessUrlRewriteWorkaround']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}

