<?php

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;


/**
 * show command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class ShowCommand extends BaseCommand
{
    const GIT_SHOW = 'show';

    /**
     * @return ShowCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * build the show command
     *
     * @param string|\GitElephant\Objects\Commit $ref the reference for the show command
     *
     * @return string
     */
    public function showCommit($ref)
    {
        $this->clearAll();

        $this->addCommandName(self::GIT_SHOW);
        $this->addCommandArgument('-s');
        $this->addCommandArgument('--pretty=raw');
        $this->addCommandArgument('--no-color');
        $this->addCommandSubject($ref);

        return $this->getCommand();
    }
}
