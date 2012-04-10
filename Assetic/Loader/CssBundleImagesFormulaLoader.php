<?php
/*
 * This file is part of the SmurfyAsseticCssBundleImagesBundle package.
 *
 * (c) smurfy <https://github.com/smurfy>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Smurfy\AsseticCssBundleImagesBundle\Assetic\Loader;

use Assetic\Factory\Resource\IteratorResourceInterface;
use Assetic\Factory\Resource\ResourceInterface;
use Assetic\Factory\Loader\FormulaLoaderInterface;

/**
 * Formular Loader Class for the embedded images in the css
 */
class CssBundleImagesFormulaLoader implements FormulaLoaderInterface
{
    /**
     * Gets the formulas form the resouce
     * 
     * @param ResourceInterface $resources The resource
     * 
     * @return array
     */
    public function load(ResourceInterface $resources)
    {
        return $resources->getContent();
    }
}