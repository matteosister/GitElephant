<?php

/**
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

use GitElephant\Objects\TreeBranch;


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

    static public function preg_split_array($array, $regexp)
    {
        $lineNumbers = array();
        $arrOut      = array();
        foreach ($array as $i => $line) {
            if (preg_match($regexp, $line)) {
                $lineNumbers[] = $i;
            }
        }

        foreach ($lineNumbers as $i => $lineNum) {
            if (isset($lineNumbers[$i + 1])) {
                $arrOut[] = array_slice($array, $lineNum, $lineNumbers[$i + 1] - $lineNum);
            } else {
                $arrOut[] = array_slice($array, $lineNum);
            }
        }

        return $arrOut;
    }
}
