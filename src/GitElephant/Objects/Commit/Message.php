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

namespace GitElephant\Objects\Commit;

/**
 * Represents a commit message
 *
 * @author Mathias Geat <mathias@ailoo.net>
 */
class Message
{
    private $message;

    /**
     * Class constructor
     *
     * @param array|string $message Message lines
     */
    public function __construct($message)
    {
        if (is_array($message)) {
            $this->message = $message;
        } else {
            $this->message = array();
            $this->message = (string) $message;
        }
    }

    /**
     * Short message equals first message line
     *
     * @return string|null
     */
    public function getShortMessage()
    {
        return $this->toString();
    }

    /**
     * Full commit message
     *
     * @return string|null
     */
    public function getFullMessage()
    {
        return $this->toString(true);
    }

    /**
     * Return message string
     *
     * @param bool $full get the full message
     *
     * @return string|null
     */
    public function toString($full = false)
    {
        if (count($this->message) == 0) {
            return null;
        }

        if ($full) {
            return implode(PHP_EOL, $this->message);
        } else {
            return $this->message[0];
        }
    }

    /**
     * String representation equals short message
     *
     * @return string|null
     */
    public function __toString()
    {
        return $this->toString();
    }
}