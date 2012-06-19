<?php
/*
 * This file is part of the SmurfyAsseticCssBundleImagesBundle package.
 *
 * (c) smurfy <https://github.com/smurfy>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Smurfy\AsseticCssBundleImagesBundle\Assetic\Resource;

use Assetic\Factory\Resource\ResourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;

/**
 * The css Resource
 */
class CssBundleImagesResource implements ResourceInterface
{
    private $kernel;
    private $options;
    private $filters;
    private $af;
    private $container;
    private $templates;
    private $engine;
    private $files;

    /**
     * Constructor.
     *
     * @param KernelInterface    $kernel    The kernel is used to parse bundle notation
     * @param AssetFactory       $af        Assetic Factory
     * @param ContainerInterface $container The Service Container
     * @param array              $options   Options for this filter
     * @param array              $filters   Additional filters for embeded images
     *
     * @return void
     */
    public function __construct(KernelInterface $kernel, $af, ContainerInterface $container, $options = array(), $filters = array())
    {
        $this->kernel = $kernel;
        $this->af = $af;
        $this->container = $container;
        $this->options = $options;
        $this->filters = $filters;
    }

    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * Overwritten sleep method, because assetics cache feature serializes this class and the embedded
     * assetFactory does not like it.
     *
     * @return array
     */
    public function __sleep()
    {
        return array('files');
    }

    /**
     * Checks if the cached data is fresh
     *
     * @param int $timestamp A Timestamp
     *
     * @return boolean
     */
    public function isFresh($timestamp)
    {
        if (!isset($this->templates) || !isset($this->engine)) {
            return true;
        }

        $files = $this->_getFiles();
        if (isset($files)) {
             foreach ($files as $file) {
                if (file_exists($file) && filemtime($file) <= $timestamp === false) {
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Creates all Assets and the formulas
     *
     * @return array
     */
    public function getContent()
    {
        $files = $this->_getFiles();

        $options = $this->options;
        $filters = $this->filters;
        $formulas = array();
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $assetFilters = array();
            if (isset($filters[$ext])) {
                $assetFilters = $filters[$ext];
            }
            $id = $this->af->generateAssetName($file, $assetFilters, $options);
            $formulas[$id] = array($file, $assetFilters, $options);
        }
        return $formulas;
    }

    /**
     * Collects all final css files if they using "cssbundleimages" as filter
     *
     * @return array
     */
    public function _getSourceContent()
    {
        $neededFormulas = array();
        foreach ($this->templates as $tpl) {
            if ($tpl instanceof ResourceInterface) {
                $formulas = $this->engine->load($tpl);
                foreach ($formulas as $formula) {
                    if (isset($formula[1])) {
                        foreach ($formula[1] as $filter) {
                            if ($filter == 'cssbundleimages') {
                                $neededFormulas[] = $formula;
                            }
                        }
                    }
                }
            }
        }

        $sourceContent = array();

        foreach ($neededFormulas as $formula) {
            $tplFilters = array_diff($formula[1], array('cssbundleimages'));
            $asset = $this->af->createAsset($formula[0], $tplFilters, $formula[2]);
            $sourceContent[] = $asset->dump();
        }

        return $sourceContent;
    }

    /**
     * Parses the sourcecontent and collects the formulas
     *
     * @return array
     */
    public function _getFiles()
    {
        if (empty($this->files)) {
            $this->files = array();

            $sourceContent = $this->_getSourceContent();

            foreach ($sourceContent as $content) {
                foreach ($this->_getFilesInCss($content) as $file) {
                    $this->files[] = $file;
                }
            }
        }
        return $this->files;
    }


    /**
     * Parses the css file and extracts all embedded images
     *
     * @param string $content Css Contnet
     *
     * @return array
     */
    private function _getFilesInCss($content)
    {
        $files = array();

        preg_match_all('/url\((["\']?)(?<url>.*?)(\\1)\)/', $content, $matches);
        foreach ($matches['url'] as $url) {
            $file = null;

            $fileUrl = $this->container->getParameterBag()->resolveValue($url);
            if ($fileUrl != $url) {
                if ('@' == $fileUrl[0] && false !== strpos($fileUrl, '/')) {
                    $url = $fileUrl;
                } else {
                    if (file_exists($fileUrl)) {
                        $file = realpath($fileUrl);
                    }
                }
            }

            if ('@' == $url[0] && false !== strpos($url, '/')) {
                $bundle = substr($url, 1);
                if (false !== $pos = strpos($bundle, '/')) {
                    $bundle = substr($bundle, 0, $pos);
                }
                try {
                    $file = $this->kernel->locateResource($url);
                } catch (\Exception $e) {
                }
            }

            if ($file) {
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * Returns the name of the resource
     *
     * @return string
     */
    public function __toString()
    {
        return 'cssbundleimages';
    }
}
