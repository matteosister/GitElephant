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
use GitElephant\Objects\Node;
use GitElephant\GitBinary;


/**
 * Tree
 *
 * Wrapper for the tree handler commands
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Tree extends BaseCommand implements \ArrayAccess, \Iterator, \Countable
{
    private $sha;
    private $children = array();
    private $position = 0;

    public function __construct($sha)
    {
        $this->sha = $sha;
        $this->position = 0;
    }

    public function lsTree($subject)
    {
        $this->addCommandName('ls-tree');
        $this->addCommandSubject($subject);
        return $this->getCommand();
    }

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

    public function count()
    {
        return count($this->children);
    }


}
