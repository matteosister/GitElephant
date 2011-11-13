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

use GitElephant\Command\LsTreeCommand;
use GitElephant\Command\Caller;
use GitElephant\Objects\NestedTreeObject;

/**
 * NestedTreeNode
 *
 * Nested Tree Node
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class NestedTreeBlob extends NestedTreeObject
{
    public function __construct($line)
    {
        $this->setAttributes($line);
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
