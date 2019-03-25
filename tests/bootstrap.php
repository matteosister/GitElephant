<?php

declare(strict_types=1);

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new \RuntimeException('Install dependencies to run test suite.');
}
require_once $file;
require_once __DIR__.'/GitElephant/TestCase.php';

echo exec('git --version')."\n";

date_default_timezone_set('UTC');
