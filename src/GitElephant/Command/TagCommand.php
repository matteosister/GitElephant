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

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;


/**
 * TagCommand
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TagCommand extends BaseCommand
{
    const TAG_COMMAND = 'tag';

    /**
     * Create a new tag
     *
     * @param string $name The new tag name
     * @param string|null $startPoint the new tag start point.
     * @param null $message the tag message
     * @return string the command
     */
    public function create($name, $startPoint = null, $message = null)
    {
        $this->clearAll();
        $this->addCommandName(self::TAG_COMMAND);
        if ($message != null) {
            $this->addCommandArgument(sprintf('-m %s', $message));
        }
        $subject = $startPoint == null ? $name : $name.' '.$startPoint;
        $this->addCommandSubject($subject);
        return $this->getCommand();
    }

    /**
     * Lists tags
     *
     * @return string the command
     */
    public function lists()
    {
        $this->clearAll();
        $this->addCommandName(self::TAG_COMMAND);
        return $this->getCommand();
    }

    /**
     * Delete a tag
     *
     * @param $name The tag to delete
     * @return string the command
     */
    public function delete($name)
    {
        $this->clearAll();
        $this->addCommandName(self::TAG_COMMAND);
        $this->addCommandArgument('-d');
        $this->addCommandSubject($name);
        return $this->getCommand();
    }
}
