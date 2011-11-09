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
    /**
     * Init the repo
     * @return string
     */
    public function init()
    {
        $this->addCommandName('init');
        return $this->getCommand();
    }

    /**
     * Add a node to the repository
     * @param string $what
     * @return string
     */
    public function add($what = '.')
    {
        $this->addCommandName('add');
        $this->addCommandSubject($what);
        return $this->getCommand();
    }

    /**
     * Commit
     * @param $message
     * @param bool $all
     * @return string
     */
    public function commit($message, $all = false) {
        if (trim($message) == '' || $message == null) {
            throw new \InvalidArgumentException(sprintf('You can\'t commit whitout message'));
        }
        $this->addCommandName('commit');
        if ($all) {
            $this->addCommandArgument('-a');
        }
        $this->addCommandArgument('-m');
        $this->addCommandSubject("'".$message."'");
        return $this->getCommand();
    }
}
