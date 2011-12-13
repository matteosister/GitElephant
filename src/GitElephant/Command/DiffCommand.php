<?php

/**
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
use GitElephant\Objects\Commit;

/**
 * Diff command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffCommand extends BaseCommand
{
    const DIFF_COMMAND = 'diff';

    /**
     * build a diff command
     *
     * @param mixed $of   the reference to diff
     * @param null  $with the source refernce to diff with $of, if not specified is the current HEAD
     * @param null  $path the path to diff, if not specified the full repository
     *
     * @return string
     */
    public function diff($of = null, $with = null, $path = null)
    {
        $this->clearAll();
        $this->addCommandName(self::DIFF_COMMAND);
        $this->addCommandArgument('--full-index');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--dst-prefix=DST/');
        $this->addCommandArgument('--src-prefix=SRC/');

        $subject = $of;

        if ($with != null) {
            $subject .= ' ' . $with;
        }
        if ($path != null) {
            $subject .= ' ' . $path;
        }
        $this->addCommandSubject($subject);
        return $this->getCommand();
    }

    /**
     * build a diff command of a commit with its parent
     *
     * @param \GitElephant\Objects\Commit $commit the commit object
     * @param string                      $path   the path to consider in diff
     *
     * @return string
     */
    public function commitDiff(Commit $commit, $path)
    {
        $this->clearAll();
        $this->addCommandName(self::DIFF_COMMAND);
        $this->addCommandArgument('--full-index');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--dst-prefix=DST/');
        $this->addCommandArgument('--src-prefix=SRC/');

        $subject = $commit->getParent() . ' ' . $commit->getSha();
        if ($path != null) {
            $subject .= ' ' . $path;
        }

        $this->addCommandSubject($subject);
        return $this->getCommand();
    }
}
