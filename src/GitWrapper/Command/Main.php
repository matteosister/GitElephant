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
use GitWrapper\GitBinary;

/**
 * Init
 *
 * Issue init command
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class Main extends BaseCommand
{
    public function init()
    {
        $this->addCommandName('init');
        return $this->getCommand();
    }
}
