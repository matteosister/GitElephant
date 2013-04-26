<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Command
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;
use GitElephant\Objects\TreeObject;
use GitElephant\Objects\TreeBranch;
use GitElephant\Objects\TreeishInterface;

/**
 * Log command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class LogCommand extends BaseCommand
{
    const GIT_LOG = 'log';

    /**
     * @return LogCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * Build an object log command
     *
     * @param \GitElephant\Objects\TreeObject             $obj    the TreeObject to get the log for
     * @param \GitElephant\Objects\TreeBranch|string|null $branch the branch to consider
     * @param int|null                                    $limit  limit to n entries
     * @param int|null                                    $offset skip n entries
     *
     * @return string
     */
    public function showObjectLog(TreeObject $obj, $branch = null, $limit = null, $offset = null)
    {
        $subject = null;
        if (null !== $branch) {
            if ($branch instanceof TreeBranch) {
                $subject .= $branch->getName();
            } else {
                $subject .= (string) $branch;
            }
        }

        return $this->showLog($subject, $obj->getFullPath(), $limit, $offset);
    }

    /**
     * Build a generic log command
     *
     * @param \GitElephant\Objects\TreeishInterface|string $ref    the reference to build the log for
     * @param string|null                                  $path   the physical path to the tree relative to the repository root
     * @param int|null                                     $limit  limit to n entries
     * @param int|null                                     $offset skip n entries
     *
     * @return string
     */
    public function showLog($ref, $path = null, $limit = null, $offset = null)
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

        if ($ref instanceof TreeishInterface) {
            $ref = $ref->getSha();
        }

        if (null !== $path && !empty($path)) {
            $this->addPath($path);
        }

        $this->addCommandSubject($ref);

        return $this->getCommand();
    }
}
