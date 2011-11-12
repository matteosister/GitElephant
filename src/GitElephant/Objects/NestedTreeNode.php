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
 * NestedTreeNode
 *
 * Nested Tree Node
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class NestedTreeNode
{
    const TYPE_BLOB = 'blob';
    const TYPE_TREE = 'tree';

    private $line;
    private $parent;
    private $permissions;
    private $type;
    private $sha;
    private $name;

    public function __construct($line, $parent = null)
    {
        $this->line = $line;
        $this->parent = $parent;

        preg_match('/(\d+)\ (\w+)\ ([a-z0-9]+)\t(.*)/', $line, $matches);
        $this->permissions = $matches[1];
        $this->type = $matches[2] == self::TYPE_TREE ? self::TYPE_TREE : self::TYPE_BLOB;
        $this->sha = $matches[3];
        $this->name = $matches[4];
    }

    public function getName()
    {
        return $this->name;
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
}
