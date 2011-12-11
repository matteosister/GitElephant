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
 * TreeObject
 *
 * generic class for tree objects
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeObject
{
    const TYPE_BLOB = 'blob';
    const TYPE_TREE = 'tree';
    const TYPE_LINK = 'commit';

    private $permissions;
    private $type;
    private $sha;
    private $name;
    private $path;


    public function __construct($permissions, $type, $sha, $name, $path)
    {
        $this->permissions = $permissions;
        $this->type = $type;
        $this->sha = $sha;
        $this->name = $name;
        $this->path = $path;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function output($basePath, $html = true)
    {
        switch($this->type)
        {
            case self::TYPE_BLOB:
                $content = file_get_contents($basePath.DIRECTORY_SEPARATOR.$this->path);
                return ('' == $content) ? 'empty file' : $content;
                break;
        }
    }

    public function isTree()
    {
        return self::TYPE_TREE == $this->getType();
    }
    public function isLink()
    {
        return self::TYPE_LINK == $this->getType();
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

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
