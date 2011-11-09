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

namespace GitWrapper\Command;

use GitWrapper\Command\BaseCommand;


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
}
