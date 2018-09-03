<?php

namespace BaruchScheduling\Functions;

function concatJsonFileTarget(string $path, string $file_name): string
{
    return "$path/$file_name.json";
}

function loadJson(string $full_path): string
{
    return file_get_contents($full_path);
}

function jsonDecodeMacro(string $path, string $file_name)
{
    return json_decode(
        loadJson(
            concatJsonFileTarget(
                $path,
                $file_name
            )
        )
    );
}
