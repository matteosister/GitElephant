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

use GitElephant\Command\BaseCommand,
    GitElephant\Objects\Commit;

/**
 * DiffTreeCommand
 *
 * diff-tree command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffTreeCommand extends BaseCommand
{
    const DIFF_TREE_COMMAND = 'diff-tree';

    public function rootDiff(Commit $commit)
    {
        $this->clearAll();
        $this->addCommandName(static::DIFF_TREE_COMMAND);
        $this->addCommandArgument('--cc');
        $this->addCommandArgument('--root');
        $this->addCommandArgument('--dst-prefix=DST/');
        $this->addCommandArgument('--src-prefix=SRC/');
        $this->addCommandSubject($commit);
        return $this->getCommand();
    }
}
