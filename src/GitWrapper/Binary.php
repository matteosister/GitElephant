<?php
/**
 * User: matteo
 * Date: 08/11/11
 * Time: 12.28
 *
 * Just for fun...
 */

/*
 * This file is part of the GitWrapper package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Binary
 *
 * It contains the reference to the system git command
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */

namespace GitWrapper;

class Binary
{
    private $path;

    public function __construct($path = null)
    {
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
