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

namespace GitElephant\Objects;


/**
 * TreeTag
 *
 * An object representing a git tag
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeTag
{
    private $name;
    private $fullRef;

    public function __construct($line)
    {
        $this->name = $line;
        $this->fullRef = 'refs/tags/'.$this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setFullRef($fullRef)
    {
        $this->fullRef = $fullRef;
    }

    public function getFullRef()
    {
        return $this->fullRef;
    }
}
