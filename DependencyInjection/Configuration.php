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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        
        $rootNode = $treeBuilder->root('smurfy_assetic_css_bundle_images');
        
        $rootNode
            ->children()
                ->scalarNode('output')->defaultValue('assetic/*')->end()
                ->booleanNode('absolute')->defaultTrue()->end()
                ->variableNode('filters')->end()
                ->arrayNode('bundles')->prototype('scalar')->isRequired()->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
