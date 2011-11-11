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

namespace GitElephant;

use GitElephant\Command\BaseCommand;


/**
 * LsFiles
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class LsFilesCommand extends BaseCommand
{
    public function listFiles()
    {
        $this->addCommandName('ls-files');
        return $this->getCommand();
    }
}
