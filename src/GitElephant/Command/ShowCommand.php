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
 * show command wrapper
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class ShowCommand extends BaseCommand
{
    const GIT_SHOW = 'show';

    public function showCommit($ref)
    {
        $this->clearAll();

        $this->addCommandName(self::GIT_SHOW);
        $this->addCommandArgument('-s');
        $this->addCommandArgument('--pretty=raw');
        $this->addCommandSubject($ref);
        return $this->getCommand();
    }
}
