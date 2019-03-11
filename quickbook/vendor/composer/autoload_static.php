<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit19d615b06c6cae5d3e09ddbbdee326a5
{
    public static $prefixLengthsPsr4 = array (
        'Q' => 
        array (
            'QuickBooksOnline\\API\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'QuickBooksOnline\\API\\' => 
        array (
            0 => __DIR__ . '/..' . '/quickbooks/v3-php-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit19d615b06c6cae5d3e09ddbbdee326a5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit19d615b06c6cae5d3e09ddbbdee326a5::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}