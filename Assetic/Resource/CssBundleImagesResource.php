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
    private $files;
    private $sourceFiles;
    
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
    
    /**
     * Overwritten sleep method, because assetics cache feature serializes this class and the embedded
     * assetFactory does not like it.
     * 
     * @return array
     */
    public function __sleep()
    {
        return array();
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
        $files = $this->_getFiles();
        $sourceFiles = $this->_getSourceFiles();
        if (isset($files) && !empty($sourceFiles)) {
            foreach ($sourceFiles as $file) {
                if (file_exists($file) && filemtime($file) <= $timestamp === false) {
                    return false;
                }
            }
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
        $options = $this->options;
        $filters = $this->filters;
        
        $formulas = array();
        $files = $this->_getFiles();
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
     * Get all image files found
     * 
     * @return array
     */
    private function _getFiles()
    {
        if (!isset($this->files)) {
            $this->_findResources();
        }
        
        return $this->files;
    }
    
    /**
     * Get all sourcefiles which contains images
     * 
     * @return array
     */
    private function _getSourceFiles()
    {
        if (!isset($this->sourceFiles)) {
            $this->_findResources();
        }
        
        return $this->sourceFiles;
    }

    /**
     * Collectes all css source files and all embedded img files
     * 
     * @return void
     */
    private function _findResources()
    {
        if (!isset($this->kernel)) {
            $this->files = array();
            $this->sourceFiles = array();
            return;
        }
        
        $bundles = $this->kernel->getBundles();
        $files = array();
        $sourceFiles = array();
        foreach ($bundles as $bundle) {
            $path = $bundle->getPath();
            $dir = $path . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'public';
            if (is_dir($dir)) {
                $finder = new Finder();
                foreach ($finder->in($dir)->files()->name('*.css') as $file) {
                    $currentFiles = $this->_getFilesInCss($file->getRealPath());
                    if (!empty($currentFiles)) {
                        $sourceFiles[] = $file->getRealPath();
                        $files = array_merge($files, $currentFiles);
                    }
                }
            }
        }
        
        $files = array_values(array_unique($files));
        $this->files = $files;
        $this->sourceFiles = $sourceFiles;
    }

    /**
     * Parses the css file and extracts all embedded images
     * 
     * @param string $cssFile Filename and Full Path to css file to parse
     * 
     * @return array
     */
    private function _getFilesInCss($cssFile)
    {
        $content = file_get_contents($cssFile);
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
