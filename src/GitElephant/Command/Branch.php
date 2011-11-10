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

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;


/**
 * Branch
 *
 *
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Branch extends BaseCommand
{
    public function create($name, $startPoint = null)
    {
        $this->addCommandName('branch');
        $subject = $startPoint == null ? $name : $name.' '.$startPoint;
        $this->addCommandSubject($subject);
        return $this->getCommand();
    }

    public function lists()
    {
        $this->addCommandName('branch');
        $this->addCommandArgument('-v');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--no-abbrev');
        return $this->getCommand();
    }

    public function delete($name)
    {
        $this->addCommandName('branch');
        $this->addCommandArgument('-d');
        $this->addCommandSubject($name);
        return $this->getCommand();
    }
}
