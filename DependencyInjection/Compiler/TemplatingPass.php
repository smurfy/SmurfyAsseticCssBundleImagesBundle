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
use Symfony\Component\DependencyInjection\Reference;

class TemplatingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_manager')) {
            return;
        }

        $engines = $container->getParameterBag()->resolveValue($container->getParameter('templating.engines'));

        $ourResource = $container->getDefinition('smurfy.assetic.css_resource');

        if (in_array('twig', $engines)) {
            $services = array();
            foreach ($container->findTaggedServiceIds('assetic.templating.twig') as $id => $attr) {
                $services[] = new Reference($id);
            }
            $ourResource->addMethodCall('setTemplates', array($services));
            $ourResource->addMethodCall('setEngine', array( new Reference('assetic.twig_formula_loader')));
        }

        if (in_array('php', $engines)) {
            $services = array();
            foreach ($container->findTaggedServiceIds('assetic.templating.php') as $id => $attr) {
                $services[] = new Reference($id);
            }
            $ourResource->addMethodCall('setTemplates', array($services));
            $ourResource->addMethodCall('setEngine', array( new Reference('assetic.php_formula_loader')));
        }
    }
}
