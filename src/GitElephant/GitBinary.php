<?php

/*
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


/**
 * Binary
 *
 * It contains the reference to the system git binary
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */


class GitBinary
{
    private $path;

    public function __construct($path = null)
    {
        if ($path == null) {
            $path = '/usr/local/bin/git';
        }
        $this->setPath($path);
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }
}
