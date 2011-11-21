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
 * DiffCommand
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffCommand extends BaseCommand
{
    const DIFF_COMMAND = 'diff';

    /**
     * @param $treeish the reference to diff
     * @param null $srcTreeish the source refernce to diff with $treeish, if not specified is the current HEAD
     * @param null $path the path to diff, if not specified the full repository
     */
    public function diff($treeish, $srcTreeish = null, $path = null)
    {
        $this->clearAll();
        $this->addCommandName(self::DIFF_COMMAND);
        $this->addCommandArgument('--full-index');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--dst-prefix=DST/');
        $this->addCommandArgument('--src-prefix=SRC/');

        $subject = $treeish;
        if ($srcTreeish != null) {
            $subject .= ' '.$srcTreeish;
        }
        if ($path != null) {
            $subject .= ' '.$path;
        }
        $this->addCommandSubject($subject);
        return $this->getCommand();
    }
}
