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

namespace GitWrapper\Objects;


/**
 * Node
 *
 * Represent a node in a git tree
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Node
{
    const TYPE_BLOB = 'blob';
    const TYPE_TREE = 'tree';


    private $permissions;
    private $type;
    private $sha;
    private $filename;

    public function __construct($line)
    {
        $arr = explode(" ", $line);
        $this->setPermissions(trim($arr[0]));
        $this->setType(trim($arr[1]));
        list($sha, $filename) = explode("\t", $arr[2]);
        $this->setSha(trim($sha));
        $this->setFilename(trim($filename));
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setSha($sha)
    {
        $this->sha = $sha;
    }

    public function getSha()
    {
        return $this->sha;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }


}
