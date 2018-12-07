<?php

$directory_to_functions = __DIR__ . "/src/Functions";

$get_filenames_for_functions = function (string $directory_to_functions) {
    $files_to_be_excluded = [".", "..", ".DS_Store"];
    return array_diff(scandir($directory_to_functions), $files_to_be_excluded);
};

$include_file = function (string $file) use ($directory_to_functions) {
    include_once "$directory_to_functions/$file";
};

array_map($include_file, $get_filenames_for_functions($directory_to_functions));
