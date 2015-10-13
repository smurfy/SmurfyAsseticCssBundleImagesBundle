<?php
/*
 * This file is part of the SmurfyAsseticCssBundleImagesBundle package.
 *
 * (c) smurfy <https://github.com/smurfy>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Smurfy\AsseticCssBundleImagesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class TemplatingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_manager')) {
            return;
        }

        $engines = $container->getParameterBag()->resolveValue($container->getParameter('templating.engines'));

        $assetManager = $container->getDefinition('assetic.asset_manager');

        if (in_array('twig', $engines)) {
            $twigCssResource = new DefinitionDecorator('smurfy.assetic.css_resource');
            $services = array();
            foreach ($container->findTaggedServiceIds('assetic.templating.twig') as $id => $attr) {
                $services[] = new Reference($id);
            }
            $twigCssResource->addMethodCall('setTemplates', array($services));
            $twigCssResource->addMethodCall('setEngine', array( new Reference('assetic.twig_formula_loader')));
            $container->setDefinition('smurfy.assetic.css_resource.twig', $twigCssResource);
            $assetManager->addMethodCall('addResource', array(new Reference('smurfy.assetic.css_resource.twig'), 'cssbundleimages'));
        }

        if (in_array('php', $engines)) {
            $phpCssResource = new DefinitionDecorator('smurfy.assetic.css_resource');
            $services = array();
            foreach ($container->findTaggedServiceIds('assetic.templating.php') as $id => $attr) {
                $services[] = new Reference($id);
            }
            $phpCssResource->addMethodCall('setTemplates', array($services));
            $phpCssResource->addMethodCall('setEngine', array( new Reference('assetic.php_formula_loader')));
            $container->setDefinition('smurfy.assetic.css_resource.php', $phpCssResource);
            $assetManager->addMethodCall('addResource', array(new Reference('smurfy.assetic.css_resource.php'), 'cssbundleimages'));
        }
    }
}
