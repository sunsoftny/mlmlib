<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit33285894728eff2b07f14c9cd0fd403d
{
    public static $classMap = array (
        'BlockIo' => __DIR__ . '/../..' . '/lib/block_io.php',
        'BlockKey' => __DIR__ . '/../..' . '/lib/block_io.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit33285894728eff2b07f14c9cd0fd403d::$classMap;

        }, null, ClassLoader::class);
    }
}
