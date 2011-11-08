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

namespace GitWrapper;

use GitWrapper\Command\Tree\Tree;
use GitWrapper\GitBinary;

/**
 * Repository
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Repository
{
    private $path;
    private $binary;
    private $tree;

    public function __construct($repository_path, GitBinary $binary = null)
    {
        if (!is_dir($repository_path)) {
            throw new \InvalidArgumentException();
        }

        if ($binary == null) {
            $binary = new GitBinary('/usr/local/bin/git');
        }

        $this->binary = $binary;
        $this->path = $repository_path;
    }

    /**
     * @param string $what the name of the tree, HEAD by default
     * @return GitWrapper\Command\Tree\Tree
     */
    public function getTree($what = 'HEAD')
    {
        $this->tree = new Tree($this->binary);
        $this->tree->lsTree($what);
        $this->tree->execute($this->path, null);
        return $this->tree;
    }
}
