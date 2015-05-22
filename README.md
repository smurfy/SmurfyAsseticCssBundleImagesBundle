Provides a new Assetic filter for CSS files which allows you the "@MyBundle" syntax in your CSS.
It also exposes DI Container parameters to the CSS File

Example 1 - Using the @MyBundle syntax:
---------------------------------------

``background: url(@MyBundle/Resources/public/images/backgrounds.png);``

Example 2 - Using the DI Container syntax:
------------------------------------------

``background: url(%kernel.root_dir%/../vendor/twitter/bootstrap/img/sprite-map.png);``

Example 3 - You even can combine both:
--------------------------------------

``background: url(@MyBundle/Resources/public/images/%site.mood%/backgrounds.png);``


It also converts all images to assets and allows you to use an existing asset filter for it (like optipng for pngs)

Installation
============

Symfony 2.1.x
-------------

Add the require line to composer.json
-------------------------------------

    "require": {
        ....
        "smurfy/asseticcssbundleimages-bundle": "dev-master"
        ...
    }

and update composer::

    php composer.phar update

Add SmurfyAsseticCssBundleImagesBundle to your application kernel
-----------------------------------------------------------------

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            // ...
            new Smurfy\AsseticCssBundleImagesBundle\SmurfyAsseticCssBundleImagesBundle(),
            // ...
        );
    }

Symfony 2.0.x
-------------

Add SmurfyAsseticCssBundleImagesBundle to your vendor/bundles/ dir
-------------------------------------------------------------------

Add the following lines in your ``deps`` file::

    [SmurfyAsseticCssBundleImagesBundle]
        git=git://github.com/smurfy/SmurfyAsseticCssBundleImagesBundle.git
        target=bundles/Smurfy/AsseticCssBundleImagesBundle

Run the vendors script::

    ./bin/vendors install

Add the Smurfy namespace to your autoloader
-------------------------------------------

    // app/autoload.php
    $loader->registerNamespaces(array(
        'Smurfy' => __DIR__.'/../vendor/bundles',
        // your other namespaces
    );

Add SmurfyAsseticCssBundleImagesBundle to your application kernel
-----------------------------------------------------------------

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            // ...
            new Smurfy\AsseticCssBundleImagesBundle\SmurfyAsseticCssBundleImagesBundle(),
            // ...
        );
    }
    
Configuration
=============

By default the filter outputs all files to /assetic/ but you can change that, also you can specify which filter should be used by extention.

    smurfy_assetic_css_bundle_images:
        output: assetic/*
        absolute: true
        lessUrlRewriteWorkaround: false
        filters:
            png:
                - optipng
            jpg:
                - jpegoptim

Sample Usage
============

In your twig template enable the filter

    {% stylesheets
        '@MyBundle/Resources/public/css/*' filter='cssbundleimages' output='assetic/*.css'
    %}
        <link rel="stylesheet" href="{{ asset_url }}" />
    {% endstylesheets %}
    
After that you can use inside your css file something like that:

    .body {
        background: url(@MyBundle/Resources/public/images/backgrounds.png);
    }

LESS Support
============

Less is fully supported. Just make sure you load the less filter before the ```cssbundleimages``` filter.

Be aware, that less does url rewrites of "url" tags if you import other less files in subdirectories and the url tag uses relative aka not beginning with / urls.
After that the ```cssbundleimages``` filter does no longer work, because the url not longer begins with ```@BundleName``` but with ```subdirectory/@BundleName```.
There is the ```lessUrlRewriteWorkaround``` config parameter which allows you to use ```/@BundleName```.

    .body {
        background: url(/@MyBundle/Resources/public/images/backgrounds.png);
    }

This way less does no url rewrites, because it detects the url as absolute.
    
Final Notes
===========

Assetic Controller support is working but it always rescans all css files which of course is not so fast.
It is recommended to use ```assetic:watch``` (```assetic:dump --watch``` on old versions of assetic bundle)
For my Project its ok but it could be a bottleneck in a large development environment.
