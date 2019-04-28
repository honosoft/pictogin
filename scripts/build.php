<?php

require_once __DIR__ . '/../vendor/autoload.php';


class Build {
    const FOLDERS = [
        'app',
        'configs',
        'public',
        'vendor',
        '.htaccess'
    ];
    const DEST = "output";
    const FILES_TO_IGNORE = ['configs/*.sample.php'];

    public function Run() {
        $root = dirname(__DIR__);
        try {
            $dst = static::pathCombine($root, static::DEST);

            if (file_exists($dst)) {
                static::recursiveDelete($dst);
            }
            mkdir($dst);

            foreach (static::FOLDERS as $folder) {
                $src = static::pathCombine($root, $folder);
                static::recursiveCopy($src, static::pathCombine($dst, basename($src)));
            }

            foreach (static::FILES_TO_IGNORE as $pattern) {
                $src = static::pathCombine($root, "output/$pattern");
                foreach (glob($src) as $file) {
                    unlink($file);
                }
            }

            // TODO: add a zip?
        } catch (\Exception $e) {
            error_log("Could not execute the build. " . $e->getMessage());
        }
    }

    private static function pathCombine($leftPart, $rightPart) { // TODO: use a variadic if we extract it from here. Path.combine(...);
        return join(DIRECTORY_SEPARATOR, [$leftPart, $rightPart]);
    }

    private static function recursiveCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) {
                    static::recursiveCopy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                } else {
                    copy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir);
    }

    private static function recursiveDelete($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    static::recursiveDelete($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }
}

$build = new Build();
$build->run();