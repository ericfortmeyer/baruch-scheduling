<?php

namespace BaruchScheduling;

class FunctionAutoLoader
{
    protected const DONT_INCLUDE = [
        ".",
        "..",
        ".DS_Store"
    ];

    protected const PATH_TO_FUNCTIONS = "src/Functions/";

    public static function loadFunctions()
    {
        array_map(
            function ($file) {
                include_once self::PATH_TO_FUNCTIONS . $file;
            },
            array_filter(
                scandir(self::PATH_TO_FUNCTIONS),
                function ($file) {
                    return !in_array($file, self::DONT_INCLUDE);
                }
            )
        );
    }

}
