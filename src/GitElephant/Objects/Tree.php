<?php

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects
 *
 * Just for fun...
 */

namespace GitElephant\Objects;

use GitElephant\Command\BaseCommand;
use GitElephant\Command\Caller;
use GitElephant\Objects\TreeObject;
use GitElephant\GitBinary;
use GitElephant\Utilities;
use GitElephant\Repository;


/**
 * An abstraction of a git tree
 *
 * Retrieve an object with array access, iterable and countable
 * with a collection of TreeObject at the given path of the repository
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Tree implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var string
     */
    private $ref;

    /**
     * the cursor position
     *
     * @var int
     */
    private $position;

    /**
     * the tree path
     *
     * @var string
     */
    private $path;

    /**
     * tree children
     *
     * @var array
     */
    private $children = array();

    /**
     * tree path children
     *
     * @var array
     */
    private $pathChildren = array();

    /**
     * the blob of the actual tree
     *
     * @var \GitElephant\Objects\TreeObject
     */
    private $blob;

    /**
     * Some path examples:
     *    empty string for root
     *    folder1/folder2
     *    folder1/folder2/filename
     *
     * @param \GitElephant\Repository $repository the repository
     * @param string                  $ref        a treeish reference
     * @param string|TreeObject       $path       the (physical) path of the repository relative to the root or TreeObject instance
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Repository $repository, $ref = 'HEAD', $path = '')
    {
        $this->position   = 0;
        $this->repository = $repository;
        $this->ref = $ref;

        if ($path instanceof TreeObject) {
            $this->path = $path->getPath();
        } else if (is_string($path)) {
            $this->path = $path;
        } else {
            throw new \InvalidArgumentException('the path for a Tree instance should be a string or a TreeObject instance');
        }
        $this->createFromCommand();
    }

    /**
     * get the commit properties from command
     *
     * @see LsTreeCommand::tree
     */
    private function createFromCommand()
    {
        $command = $this->getRepository()->getContainer()->get('command.ls_tree')->tree($this->ref);
        $outputLines = $this->getCaller()->execute($command, true, $this->getRepository()->getPath())->getOutputLines();
        $this->parseOutputLines($outputLines);
    }

    /**
     * parse the output of a git command showing a ls-tree
     *
     * @param array $outputLines output lines
     */
    private function parseOutputLines($outputLines)
    {
        foreach ($outputLines as $line) {
            $this->parseLine($line);
        }
        usort($this->children, array($this, 'sortChildren'));
        $this->scanPathsForBlob($outputLines);
    }

    /**
     * @return \GitElephant\Command\Caller
     */
    private function getCaller()
    {
        return $this->getRepository()->getCaller();
    }

    /**
     * get the current tree parent, null if root
     *
     * @return null|string
     */
    public function getParent()
    {
        if (strrpos($this->path, '/') === false) {
            return null;
        } else {
            return substr($this->path, 0, strrpos($this->path, '/'));
        }
    }

    /**
     * tell if the tree created is the root of the repository
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->path == '';
    }

    /**
     * tell if the path given is a blob path
     *
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
     *
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
                    $bc[$i]['path']  = $pathString . $name;
                    $bc[$i]['label'] = $this->blob;
                    $pathString .= $name . '/';
                } else {
                    $bc[$i]['path']  = $pathString . $name;
                    $bc[$i]['label'] = $name;
                    $pathString .= $name . '/';
                }
            }
        }
        return $bc;
    }

    /**
     * check if the path is equals to a fullPath
     * to tell if it's a blob
     *
     * @param array $outputLines output lines
     * @return mixed
     */
    private function scanPathsForBlob($outputLines)
    {
        // no children, empty folder or blob!
        if (count($this->children) > 0) {
            return;
        }
        foreach ($outputLines as $line) {
            if ($line != '') {
                $slices = $this->getLineSlices($line);
                if ($slices['fullPath'] == $this->path) {
                    $pos = strrpos($slices['fullPath'], '/');
                    if ($pos === false) {
                        $name       = $this->path;
                        $this->path = '';
                    } else {
                        $path       = $this->path;
                        $this->path = substr($path, 0, $pos);
                        $name       = substr($path, $pos + 1);
                    }
                    $this->blob = new TreeObject($slices['permissions'], $slices['type'], $slices['sha'], $slices['size'], $name, $slices['fullPath']);
                }
            }
        }
    }

    /**
     * Reorder children of the tree
     * Tree first (alphabetically) and then blobs (alphabetically)
     *
     * @param TreeObject $a the first object
     * @param TreeObject $b the second object
     *
     * @return int
     */
    private function sortChildren(TreeObject $a, TreeObject $b)
    {
        if ($a->getType() == $b->getType()) {
            $names = array($a->getName(), $b->getName());
            sort($names);
            return ($a->getName() == $names[0]) ? -1 : 1;
        }
        return $a->getType() == TreeObject::TYPE_TREE && $b->getType() == TreeObject::TYPE_BLOB ? -1 : 1;
    }

    /**
     * Parse a single line into pieces
     *
     * @param string $line a single line output from the git binary
     *
     * @return mixed
     */
    private function parseLine($line)
    {
        if ($line == '') {
            return;
        }
        $slices = $this->getLineSlices($line);
        if ($this->isRoot()) {
            // if is root check for first children
            $pattern     = '/(\w+)\/(.*)/';
            $replacement = '$1';
        } else {
            // filter by the children of the path
            if (!preg_match(sprintf('/^%s\/(\w*)/', preg_quote($this->path, '/')), $slices['fullPath'])) {
                return;
            }
            $pattern     = sprintf('/^%s\/(\w*)/', preg_quote($this->path, '/'));
            $replacement = '$1';
        }
        $name = preg_replace($pattern, $replacement, $slices['fullPath']);
        if (strpos($name, '/') !== false) {
            return;
        }

        if (!in_array($name, $this->pathChildren)) {
            //$path = preg_replace('/(.*)(\/'.$name.')$/', '$1', $slices['fullPath']);
            $path                 = rtrim($slices['fullPath'], $name);
            $treeObject           = new TreeObject($slices['permissions'], $slices['type'], $slices['sha'], $slices['size'], $name, $path);
            $this->children[]     = $treeObject;
            $this->pathChildren[] = $name;
        }
    }

    /**
     * Take a line and actually turn in slices
     *
     * @param string $line a single line output from the git binary
     *
     * @return array
     */
    private function getLineSlices($line)
    {
        preg_match('/^(\d+) (\w+) ([a-z0-9]+) +(\d+|-)\t(.*)$/', $line, $matches);
        $permissions = $matches[1];
        $type        = null;
        switch ($matches[2]) {
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
        $sha      = $matches[3];
        $size     = $matches[4];
        $fullPath = $matches[5];

        return array(
            'permissions' => $permissions,
            'type'        => $type,
            'sha'         => $sha,
            'size'        => $size,
            'fullPath'    => $fullPath
        );
    }

    /**
     * Repository setter
     *
     * @param \GitElephant\Repository $repository the repository variable
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Repository getter
     *
     * @return \GitElephant\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Blob getter
     *
     * @return TreeObject
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * Path getter
     *
     * @return null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->children[$offset]);
    }


    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->children[$offset]) ? $this->children[$offset] : null;
    }

    /**
     * ArrayAccess interface
     *
     * @param int   $offset offset
     * @param mixed $value  value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->children[] = $value;
        } else {
            $this->children[$offset] = $value;
        }
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     */
    public function offsetUnset($offset)
    {
        unset($this->children[$offset]);
    }

    /**
     * Countable interface
     *
     * @return int|void
     */
    public function count()
    {
        return count($this->children);
    }

    /**
     * Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        return $this->children[$this->position];
    }

    /**
     * Iterator interface
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Iterator interface
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator interface
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->children[$this->position]);
    }

    /**
     * Iterator interface
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
