<?php
/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2013  Matteo Giachino
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see [http://www.gnu.org/licenses/].
 */

namespace GitElephant;

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
    public static function normalizeDirectorySeparator(string $path)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    /**
     * explode an array by lines that match a regular expression
     *
     * @param array  $list   the original array, should be a non-associative array
     * @param string $regexp the regular expression
     *
     * @return array an array of array pieces
     * @throws \InvalidArgumentException
     */
    public static function pregSplitArray(array $list, string $regexp)
    {
        if (static::isAssociative($list)) {
            throw new \InvalidArgumentException('pregSplitArray only accepts non-associative arrays.');
        }
        $lineNumbers = [];
        $arrOut = [];
        foreach ($list as $i => $line) {
            if (preg_match($regexp, $line)) {
                $lineNumbers[] = $i;
            }
        }

        foreach ($lineNumbers as $i => $lineNum) {
            if (isset($lineNumbers[$i + 1])) {
                $arrOut[] = array_slice($list, $lineNum, $lineNumbers[$i + 1] - $lineNum);
            } else {
                $arrOut[] = array_slice($list, $lineNum);
            }
        }

        return $arrOut;
    }

    /**
     * @param array  $list   a flat array
     * @param string $regexp a regular expression
     *
     * @return array
     */
    public static function pregSplitFlatArray(array $list, string $regexp)
    {
        $index = 0;
        $slices = [];
        $slice = [];
        foreach ($list as $val) {
            if (preg_match($regexp, $val) && !empty($slice)) {
                $slices[$index] = $slice;
                ++$index;
                $slice = [];
            }
            $slice[] = $val;
        }
        if (!empty($slice)) {
            $slices[$index] = $slice;
        }

        return $slices;
    }

    /**
     * Tell if an array is associative
     *
     * @param array $list an array
     *
     * @return bool
     */
    public static function isAssociative(array $list)
    {
        return array_keys($list) !== range(0, count($list) - 1);
    }
}
