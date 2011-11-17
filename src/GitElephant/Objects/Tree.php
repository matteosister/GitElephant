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
 * Retrieve an object with array access, iterable and countable
 * with a collection of TreeObject at the given path of the repository
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Tree implements \ArrayAccess, \Countable, \Iterator
{
    private $position;
    private $path;
    private $children = array();
    private $pathChildren = array();
    /**
     * @var TreeObject
     */
    private $blob;

    /**
     * Some path examples:
     *    empty string for root
     *    folder1/folder2
     *    folder1/folder2/filename
     *
     * @param $result an array with outpul lines from the Caller
     * @param null $path the (physical) path of the repository relative to the root
     */
    public function __construct($result, $path = null)
    {
        $this->position = 0;
        $this->path = $path;
        foreach($result as $line) {
            $this->parseLine($line);
        }
        usort($this->children, array($this, 'sortChildren'));
    }

    /**
     * get the current tree parent, null if root
     * @return null|string
     */
    public function getParent()
    {
        if (strrpos($this->path, '/') === FALSE) {
            return null;
        } else {
            return substr($this->path, 0, strrpos($this->path, '/'));
        }
    }

    /**
     * tell if the tree created is the root of the repository
     * @return bool
     */
    public function isRoot()
    {
        return $this->path == '';
    }

    /**
     * tell if the path given is a blob path
     * @return bool
     */
    public function isBlob()
    {
        return isset($this->blob);
    }

    /**
     * Return an array like this
     *   0 => array(
     *      'path' => the path to the current element
     *      'label' => the name of the current element
     *   ),
     *   1 => array(),
     *   ...
     * @return array
     */
    public function getBreadcrumb()
    {
        $bc = array();
        if (!$this->isRoot()) {
            $arrayNames = explode('/', $this->path);
            $pathString = '';
            foreach ($arrayNames as $i => $name) {
                if ($this->isBlob() && $name == $this->blob->getName()) {
                    $bc[$i]['path'] = $pathString.$name;
                    $bc[$i]['label'] = $this->blob;
                    $pathString .= $name.'/';
                } else {
                    $bc[$i]['path'] = $pathString.$name;
                    $bc[$i]['label'] = $name;
                    $pathString .= $name.'/';
                }
            }
        }
        return $bc;
    }

    private function sortChildren(TreeObject $a, TreeObject $b)
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
        $type = null;
        switch($matches[2]) {
            case TreeObject::TYPE_TREE:
                $type = TreeObject::TYPE_TREE;
                break;
            case TreeObject::TYPE_BLOB:
                $type = TreeObject::TYPE_BLOB;
                break;
            case TreeObject::TYPE_LINK:
                $type = TreeObject::TYPE_LINK;
                break;
        }
        $sha = $matches[3];
        $name = $matches[4];

        if ($this->isRoot()) {
            $pattern = '/(\w+)\/(.*)/';
            $replacement = '$1';
        } else {
            // if do not match I check if it's a path to a blob
            if (!preg_match(sprintf('/^%s\/(.*)/', preg_quote($this->path, '/')), $name)) {
                if ($name == $this->path) {
                    $newName = substr($name, strrpos($name, '/') + 1);
                    $this->blob = new TreeObject($permissions, $type, $sha, $newName);
                }
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

    // ArrayAccess interface
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

    // Countable interface
    public function count()
    {
        return count($this->children);
    }

    // Iterator interface
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

    public function getBlob()
    {
        return $this->blob;
    }
}
