<?php

/*
 * This file is part of the GitWrapper package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant;


/**
 * Utilities
 *
 * Utilities class
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Utilities
{
    static public function normalizeDirectorySeparator($path)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }
}
