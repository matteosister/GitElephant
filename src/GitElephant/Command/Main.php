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
use GitElephant\GitBinary;

/**
 * Init
 *
 * Issue init command
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class Main extends BaseCommand
{
    const GIT_INIT = 'init';
    const GIT_STATUS = 'status';
    const GIT_ADD = 'add';
    const GIT_COMMIT = 'commit';

    /**
     * Init the repo
     * @return Main
     */
    public function init()
    {
        $this->addCommandName(self::GIT_INIT);
        return $this->getCommand();
    }

    public function status()
    {
        $this->addCommandName(self::GIT_STATUS);
        return $this->getCommand();
    }

    /**
     * Add a node to the repository
     * @param string $what
     * @return Main
     */
    public function add($what = '.')
    {
        $this->addCommandName(self::GIT_ADD);
        $this->addCommandSubject($what);
        return $this->getCommand();
    }

    /**
     * Commit
     * @param $message
     * @param bool $all
     * @return Main
     */
    public function commit($message, $all = false) {
        if (trim($message) == '' || $message == null) {
            throw new \InvalidArgumentException(sprintf('You can\'t commit whitout message'));
        }
        $this->addCommandName(self::GIT_COMMIT);
        if ($all) {
            $this->addCommandArgument('-a');
        }
        $this->addCommandArgument(sprintf("-m '%s'", $message));
        return $this->getCommand();
    }
}
