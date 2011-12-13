<?php

/**
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
 * An object representing a node in the tree
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

    /**
     * Class constructor
     *
     * @param string $permissions node permissions
     * @param string $type        node type
     * @param string $sha         node sha
     * @param string $size        node size in bytes
     * @param string $name        node name
     * @param string $path        node path
     */
    public function __construct($permissions, $type, $sha, $size, $name, $path)
    {
        $this->permissions = $permissions;
        $this->type        = $type;
        $this->sha         = $sha;
        $this->size        = $size;
        $this->name        = $name;
        $this->path        = $path;
    }

    /**
     * toString magic method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * output the node in pretty formats
     *
     * @param string $basePath the base path of the repository
     *
     * @return string
     */
    public function output($basePath)
    {
        switch ($this->type)
        {
            case self::TYPE_BLOB:
                $content = file_get_contents($basePath . DIRECTORY_SEPARATOR . $this->path);
                return ('' == $content) ? 'empty file' : $content;
                break;
        }
    }

    /**
     * Mime Type getter
     *
     * @param string $basePath the base path of the repository
     *
     * @return string
     */
    public function getMimeType($basePath)
    {
        return mime_content_type($basePath . DIRECTORY_SEPARATOR . $this->path);
    }

    /**
     * get extension if it's a blob
     *
     * @param string $basePath the base path of the repository
     *
     * @return string|null
     */
    public function getExtension($basePath)
    {
        if ($this->type != self::TYPE_BLOB) {
            return null;
        }
        $info = pathinfo($basePath . DIRECTORY_SEPARATOR . $this->path);
        return isset($info['extension']) ? $info['extension'] : null;
    }

    /**
     * whether the node is a tree
     *
     * @return bool
     */
    public function isTree()
    {
        return self::TYPE_TREE == $this->getType();
    }

    /**
     * whether the node is a link
     *
     * @return bool
     */
    public function isLink()
    {
        return self::TYPE_LINK == $this->getType();
    }

    /**
     * Full path getter
     *
     * @return string
     */
    public function getFullPath()
    {
        if ($this->path == '') {
            return $this->name;
        } else {
            return $this->path . '/' . $this->name;
        }
    }

    /**
     * permissions getter
     *
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * sha getter
     *
     * @return string
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * type getter
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * name getter
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * path getter
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * size getter
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }
}
