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
use GitElephant\Objects\TreeObject;
use GitElephant\Objects\TreeBranch;

/**
 * Log command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class LogCommand extends BaseCommand
{
    const GIT_LOG = 'log';

    /**
     * Build an object log command
     *
     * @param \GitElephant\Objects\TreeObject               $obj    the TreeObject to get the log for
     * @param \GitElephant\Objects\TreeBranch|string|null   $branch the branch to consider
     * @param int|null                                      $limit  limit to n entries
     * @param int|null                                      $offset skip n entries
     *
     * @return string
     */
    public function showObjectLog(TreeObject $obj, $branch = null, $limit = null, $offset = null)
    {
        $subject = '';
        if (null !== $branch) {
            if ($branch instanceof TreeBranch) {
                $subject .= $branch->getName();
            } else {
                $subject .= (string) $branch;
            }
        }
        $subject .= ' -- ' . $obj->getFullPath();

        return $this->showLog($subject, $limit, $offset);
    }

    /**
     * Build a generic log command
     *
     * @param \GitElephant\Objects\TreeishInterface|string  $ref    the reference to build the log for
     * @param int|null                                      $limit  limit to n entries
     * @param int|null                                      $offset skip n entries
     *
     * @return string
     */
    public function showLog($ref, $limit = null, $offset = null)
    {
        $this->clearAll();

        $this->addCommandName(self::GIT_LOG);
        $this->addCommandArgument('-s');
        $this->addCommandArgument('--pretty=raw');
        $this->addCommandArgument('--no-color');

        if (null !== $limit) {
            $limit = (int) $limit;
            $this->addCommandArgument('--max-count=' . $limit);
        }

        if (null !== $offset) {
            $offset = (int) $offset;
            $this->addCommandArgument('--skip=' . $offset);
        }

        if ($ref instanceof \GitElephant\Objects\TreeishInterface) {
            $ref = $ref->getSha();
        }

        $this->addCommandSubject($ref);
        return $this->getCommand();
    }
}