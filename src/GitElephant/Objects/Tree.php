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
    private $children = array();

    public function __construct($result, $parent = null)
    {
        $this->position = 0;
        foreach($result as $line) {
            $this->parseLine($line);
        }
    }

    private function parseLine($line)
    {
        //040000 tree e260d84fb199a040e913816164bcea2e7d464025	pippone/pluto
        preg_match('/(\d+)\ (\w+)\ ([a-z0-9]+)\t(.*)/', $line, $matches);
        $permissions = $matches[1];
        $type = $matches[2] == 'tree' ? TreeObject::TYPE_TREE : TreeObject::TYPE_BLOB;
        $sha = $matches[3];
        $name = $matches[4];
        $treeObject = new TreeObject($permissions, $type, $sha, $name);
        $this->children[] = $treeObject;
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
