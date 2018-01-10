<?php

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Override from laravel vendor to solve the issue about frontend rebuild required for new js files
 * while phpunit does not require js
 *
 *
 * Get the path to a versioned Mix file.
 *
 * @param  string  $path
 * @param  string  $manifestDirectory
 *
 * @return \Illuminate\Support\HtmlString
 *
 * @throws \Exception
 */
function mix($path, $manifestDirectory = '')
{
    dd('here');
    static $manifests = [];

    if (! Str::startsWith($path, '/')) {
        $path = "/{$path}";
    }

    if ($manifestDirectory && ! Str::startsWith($manifestDirectory, '/')) {
        $manifestDirectory = "/{$manifestDirectory}";
    }

    if (file_exists(public_path($manifestDirectory.'/hot'))) {
        return new HtmlString("//localhost:8080{$path}");
    }

    $manifestPath = public_path($manifestDirectory.'/mix-manifest.json');

    if (! isset($manifests[$manifestPath])) {
        if (! file_exists($manifestPath)) {
            throw new Exception('The Mix manifest does not exist.');
        }

        $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
    }

    $manifest = $manifests[$manifestPath];

    if (! isset($manifest[$path])) {
        report(new Exception("Unable to locate Mix file: {$path}."));

        if (! app('config')->get('app.debug')) {
            return $path;
        }
    }

    // addon for fix
    if(preg_match('|phpunit$|', $_SERVER['SCRIPT_NAME']) && ! array_key_exists($path, $manifest)) {
        $manifest[$path] = $path;
    }

    return new HtmlString($manifestDirectory.$manifest[$path]);
}