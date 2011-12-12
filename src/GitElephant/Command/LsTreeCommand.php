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

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;
use GitElephant\Objects\TreeBranch;
use GitElephant\Objects\TreeTag;


/**
 * ls-tree command wrapper
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class LsTreeCommand extends BaseCommand
{
    const LS_TREE_COMMAND = 'ls-tree';

    public function tree($ref = 'HEAD')
    {
        $what = $ref;
        if ($ref instanceof TreeBranch || $ref instanceof TreeTag) {
            $what = $ref->getFullRef();
        }

        $this->clearAll();

        $this->addCommandName(self::LS_TREE_COMMAND);
        // recurse
        $this->addCommandArgument('-r');
        // show trees
        $this->addCommandArgument('-t');
        $this->addCommandArgument('-l');
        $this->addCommandSubject($what);
        return $this->getCommand();
    }

    public function listAll($ref = null)
    {
        if ($ref == null) $ref = 'HEAD';
        $this->clearAll();

        $this->addCommandName(self::LS_TREE_COMMAND);
        $this->addCommandSubject($ref);
        return $this->getCommand();
    }
}
