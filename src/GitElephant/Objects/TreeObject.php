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
    private $size;
    private $name;
    private $path;

    public function __construct($permissions, $type, $sha, $size, $name, $path)
    {
        $this->permissions = $permissions;
        $this->type        = $type;
        $this->sha         = $sha;
        $this->size        = $size;
        $this->name        = $name;
        $this->path        = $path;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function output($basePath, $html = true)
    {
        switch ($this->type)
        {
            case self::TYPE_BLOB:
                $content = file_get_contents($basePath . DIRECTORY_SEPARATOR . $this->path);
                return ('' == $content) ? 'empty file' : $content;
                break;
        }
    }

    public function getMimeType($basePath)
    {
        return mime_content_type($basePath . DIRECTORY_SEPARATOR . $this->path);
    }

    public function getExtension($basePath)
    {
        $info = pathinfo($basePath . DIRECTORY_SEPARATOR . $this->path);
        return isset($info['extension']) ? $info['extension'] : null;
    }

    public function isTree()
    {
        return self::TYPE_TREE == $this->getType();
    }

    public function isLink()
    {
        return self::TYPE_LINK == $this->getType();
    }

    public function getFullPath()
    {
        if ($this->path == '') {
            return $this->name;
        } else {
            return $this->path . '/' . $this->name;
        }
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getSha()
    {
        return $this->sha;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getSize()
    {
        return $this->size;
    }
}
