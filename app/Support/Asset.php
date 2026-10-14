<?php

namespace App\Support;

class Asset
{
    /** A public/ file's URL stamped with its last change, so browsers drop a stale copy. */
    public static function url(string $path): string
    {
        $file = public_path($path);

        return is_file($file) ? asset($path).'?v='.filemtime($file) : asset($path);
    }
}
