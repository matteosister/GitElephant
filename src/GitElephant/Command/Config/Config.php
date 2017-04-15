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

namespace GitElephant\Command\Config;

/**
 * Class Config
 * Simple value object that keeps a config reference
 *
 * @package GitElephant\Command\Config
 */
class Config
{
    /**
     * @var string
     */
    private $section;

    /**
     * @var string
     */
    private $key;

    /**
     * constructor
     *
     * @param string $section
     * @param string $key
     */
    private function __construct($section, $key)
    {
        $this->section = $section;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * factory method
     *
     * @param string $section
     * @param string $key
     *
     * @return Config
     */
    public static function create($section, $key)
    {
        return new self($section, $key);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return sprintf('%s.%s', $this->section, $this->key);
    }

    /**
     * Get Key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get Section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }
}
