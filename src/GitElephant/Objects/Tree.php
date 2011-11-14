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

namespace GitElephant\Objects;

use GitElephant\Command\BaseCommand;
use GitElephant\Command\Caller;
use GitElephant\Objects\TreeObject;
use GitElephant\GitBinary;
use GitElephant\Utilities;


/**
 * Tree
 *
 * Wrapper for the tree handler commands
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Tree implements \ArrayAccess, \Countable, \Iterator
{
    private $position;
    private $path;
    private $children = array();
    private $pathChildren = array();

    public function __construct($result, $path = null)
    {
        $this->position = 0;
        $this->path = $path;
        foreach($result as $line) {
            $this->parseLine($line);
        }
        usort($this->children, array($this, 'sortChildren'));
    }

    public function getParent()
    {
        if (strrpos($this->path, '/') === FALSE) {
            return null;
        } else {
            return substr($this->path, 0, strrpos($this->path, '/'));
        }
    }

    public function isRoot()
    {
        return $this->path == '';
    }

    public function getBreadcrumb()
    {
        $bc = array();
        if (!$this->isRoot()) {
            $arrayNames = explode('/', $this->path);
            $pathString = '';
            foreach ($arrayNames as $i => $name) {
                $bc[$i]['path'] = $pathString.$name;
                $bc[$i]['label'] = $name;
                $pathString .= $name.'/';
            }
        }
        return $bc;
    }

    private function sortChildren($a, $b)
    {
        if ($a->getType() == $b->getType()) {
            $names = array($a->getName(), $b->getName());
            sort($names);
            return ($a->getName() == $names[0]) ? -1 : 1;
        }
        return $a->getType() == TreeObject::TYPE_TREE && $b->getType() == TreeObject::TYPE_BLOB ? -1 : 1;
    }

    private function parseLine($line)
    {
        preg_match('/(\d+)\ (\w+)\ ([a-z0-9]+)\t(.*)/', $line, $matches);
        $permissions = $matches[1];
        $type = $matches[2] == 'tree' ? TreeObject::TYPE_TREE : TreeObject::TYPE_BLOB;
        $sha = $matches[3];
        $name = $matches[4];

        if ($this->isRoot()) {
            $pattern = '/(\w+)\/(.*)/';
            $replacement = '$1';
        } else {
            if (!preg_match(sprintf('/^%s\/(.*)/', preg_quote($this->path, '/')), $name)) {
                return;
            }
            $pattern = sprintf('/^%s\/(\w*)/', preg_quote($this->path, '/'));
            $replacement = '$1';
        }
        $newName = preg_replace($pattern, $replacement, $name);
        if (strpos($newName, '/') !== FALSE) {
            return;
        }
        if (!in_array($newName, $this->pathChildren)) {
            $this->pathChildren[] = $newName;
            $treeObject = new TreeObject($permissions, $type, $sha, $newName);
            $this->children[] = $treeObject;
        }
    }

    // ArrayAccess
    public function offsetExists($offset)
    {
        return isset($this->children[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->children[$offset]) ? $this->children[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->children[] = $value;
        } else {
            $this->children[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->children[$offset]);
    }

    // Countable
    public function count()
    {
        return count($this->children);
    }

    // Iterator
    public function current()
    {
        return $this->children[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->children[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
