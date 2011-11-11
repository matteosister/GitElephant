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


/**
 * LsTreeCommand
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class LsTreeCommand extends BaseCommand
{
    public function callLsTree($what = 'HEAD')
    {
        $this->clearAll();

        $this->addCommandName('ls-tree');
        // display the full path instead of the file name
        //$this->addCommandArgument('--full-name');
        // tree AND blobs
        //$this->addCommandArgument('-t');
        
        $this->addCommandSubject($what);
        return $this->getCommand();
    }
}
