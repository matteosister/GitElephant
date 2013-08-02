<?php

namespace GitElephant\Command\Remote;

use GitElephant\Command\SubCommandCommand;

/**
 * Class ShowRemoteCommand
 * 
 * remote subcommand generator for show
 *
 * @package GitElephant\Objects
 * @author  David Neimeyer <davidneimeyer@gmail.com>
 */

class ShowSubCommand extends SubCommandCommand
{
    const GIT_REMOTE_SHOW = 'show';

    /**
     * build show sub command
     *
     * NOTE: for technical reasons $name is optional, however under normal
     * implementation it SHOULD be passed!
     *
     * @param string $name
     *
     * @return ShowSubCommand
     */
    public function prepare($name = null)
    {
        $this->addCommandName(self::GIT_REMOTE_SHOW);
        /**
         *  only add subject if relevant,
         *  otherwise on repositories without a remote defined (ie, fresh
         *  init'd or mock) will likely trigger warning/error condition
         *
         */
        if ($name) {
            $this->addCommandSubject($name);
        }

        return $this;
    }
}
