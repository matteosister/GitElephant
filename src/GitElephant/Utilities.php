<?php

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

namespace GitElephant;

use GitElephant\Objects\TreeBranch;


/**
 * Utilities class
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Utilities
{
    /**
     * Replace / with the system directory separator
     *
     * @param string $path the original path
     *
     * @return mixed
     */
    static public function normalizeDirectorySeparator($path)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    /**
    * explode an array by lines that match a regular expression
    *
    * @param array  $array  the original array, should be a non-associative array
    * @param string $regexp the regular expression
    *
    * @return array an array of array pieces
    * @throws \InvalidArgumentException
    */
    static public function pregSplitArray($array, $regexp)
    {
        if (static::isAssociative($array)) {
            throw new \InvalidArgumentException('pregSplitArray only accepts non-associative arrays');
        }
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

    /**
     * Tell if an array is associative
     *
     * @param array $arr an array
     *
     * @return bool
     */
    static public function isAssociative($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
