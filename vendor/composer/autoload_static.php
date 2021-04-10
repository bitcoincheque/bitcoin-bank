<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3b8effb90592f648bd5ee834866fe51a
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WP_PluginFramework\\' => 19,
        ),
        'D' => 
        array (
            'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\' => 55,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WP_PluginFramework\\' => 
        array (
            0 => __DIR__ . '/..' . '/wppluginframework/wp-plugin-framework/src',
        ),
        'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\' => 
        array (
            0 => __DIR__ . '/..' . '/dealerdirect/phpcodesniffer-composer-installer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3b8effb90592f648bd5ee834866fe51a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3b8effb90592f648bd5ee834866fe51a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
