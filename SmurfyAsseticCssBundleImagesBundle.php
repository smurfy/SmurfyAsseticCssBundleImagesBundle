<?php
/*
 * This file is part of the SmurfyAsseticCssBundleImagesBundle package.
 *
 * (c) smurfy <https://github.com/smurfy>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Smurfy\AsseticCssBundleImagesBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Smurfy\AsseticCssBundleImagesBundle\DependencyInjection\Compiler\TemplatingPass;

/**
 * SmurfyAsseticCssBundleImagesBundle Class
 */
class SmurfyAsseticCssBundleImagesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TemplatingPass());
    }
}
