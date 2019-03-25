<?php

declare(strict_types=1);

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
 * @author Matteo Giachino <matteog@gmail.com>
 */
class Utilities
{
    /**
     * explode an array by lines that match a regular expression
     *
     * @param string[] $list  a flat array
     * @param string $pattern a regular expression
     *
     * @return string[]
     */
    public static function pregSplitArray(array $list, string $pattern): array
    {
        $slices = [];
        $index = -1;
        foreach ($list as $value) {
            if (preg_match($pattern, $value) === 1) {
                ++$index;
            }

            if ($index !== -1) {
                $slices[$index][] = $value;
            }
        }

        return $slices;
    }

    /**
     * @param string[] $list  a flat array
     * @param string $pattern a regular expression
     *
     * @return string[]
     */
    public static function pregSplitFlatArray(array $list, string $pattern): array
    {
        $slices = [];
        $index = -1;
        foreach ($list as $value) {
            if (preg_match($pattern, $value) === 1) {
                ++$index;
            }

            $slices[$index + 1][] = $value;
        }

        return $slices;
    }
}
