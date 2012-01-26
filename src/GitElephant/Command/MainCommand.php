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
use GitElephant\GitBinary;
use GitElephant\Objects\TreeBranch;
use GitElephant\Objects\TreeishInterface;

/**
 * Main command generator (init, status, add, commit, checkout)
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class MainCommand extends BaseCommand
{
    const GIT_INIT     = 'init';
    const GIT_STATUS   = 'status';
    const GIT_ADD      = 'add';
    const GIT_COMMIT   = 'commit';
    const GIT_CHECKOUT = 'checkout';

    /**
     * Init the repository
     *
     * @return Main
     */
    public function init()
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_INIT);
        return $this->getCommand();
    }

    /**
     * Get the repository status
     *
     * @return string
     */
    public function status()
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_STATUS);
        return $this->getCommand();
    }

    /**
     * Add a node to the repository
     *
     * @param string $what what should be added to the repository
     *
     * @return string
     */
    public function add($what = '.')
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_ADD);
        $this->addCommandSubject($what);
        return $this->getCommand();
    }

    /**
     * Commit
     *
     * @param string $message   the commit message
     * @param bool   $commitAll commit all changes
     *
     * @return string
     */
    public function commit($message, $commitAll = false)
    {
        $this->clearAll();
        if (trim($message) == '' || $message == null) {
            throw new \InvalidArgumentException(sprintf('You can\'t commit whitout message'));
        }
        $this->addCommandName(self::GIT_COMMIT);

        if ($commitAll) {
            $this->addCommandArgument('-a');
        }

        $this->addCommandArgument('-m');
        $this->addCommandSubject(sprintf("'%s'", $message));
        return $this->getCommand();
    }

    /**
     * Checkout a treeish reference
     *
     * @param string|TreeBranch $ref the reference to checkout
     *
     * @return string
     */
    public function checkout($ref)
    {
        $this->clearAll();

        $what = $ref;
        if ($ref instanceof TreeishInterface) {
            $what = $ref->getSha();
        }

        $this->addCommandName(self::GIT_CHECKOUT);
        $this->addCommandArgument('-q');
        $this->addCommandSubject($what);
        return $this->getCommand();
    }
}
