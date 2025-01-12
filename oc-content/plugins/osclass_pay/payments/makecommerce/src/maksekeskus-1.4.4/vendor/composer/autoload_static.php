<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb265a6216b766b6293dfa362de19b02d
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Maksekeskus\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Maksekeskus\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'Httpful' => 
            array (
                0 => __DIR__ . '/..' . '/nategood/httpful/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb265a6216b766b6293dfa362de19b02d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb265a6216b766b6293dfa362de19b02d::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitb265a6216b766b6293dfa362de19b02d::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
