<?php

namespace GitElephant\Objects\Commit;

/**
 * Represents a commit message
 *
 * @author Mathias Geat <mathias@ailoo.net>
 */
class Message
{
    /**
     * the message
     *
     * @var array|string
     */
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