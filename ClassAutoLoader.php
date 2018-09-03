<?php
namespace BaruchScheduling;

class ClassAutoLoader
{
    protected const EXT = ".php";
    protected const ROOT_DIR = "src";
    protected const ROOT_NAMESPACE = "BaruchScheduling";
    protected const NAMESPACE_SEPARATOR = "\\";

    public static function register(): void
    {
        spl_autoload_register(
            function ($class) {
                file_exists(self::toPath($class))
                    && include_once self::toPath($class);
            },
            false,
            false
        );
    }

    protected static function toPath(string $class): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . self::ROOT_DIR
        . self::stripRootNamespace(
            self::replaceNamespaceSeparatorWithDirectorySeparator($class)
        )
        . self::EXT;
    }

    protected static function stripRootNamespace(string $path)
    {
        return \str_replace(
            self::ROOT_NAMESPACE,
            "",
            $path
        );
    }

    protected static function replaceNamespaceSeparatorWithDirectorySeparator(string $class)
    {
        return \str_replace(
            self::NAMESPACE_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $class
        );
    }
}
