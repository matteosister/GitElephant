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

namespace GitElephant\Objects;

use GitElephant\Objects\GitAuthor;

/**
 * Git log abstraction object
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Log
{
    private $sha;
    private $author;
    private $message;
    private $datetime;

    /**
     * Class constructor
     *
     * @param array $outputLines the command output lines
     */
    public function __construct($outputLines)
    {
        $this->message = array();
        foreach ($outputLines as $line) {
            $matches = array();
            if (preg_match('/^commit (\w+)$/', $line, $matches) > 0) {
                $this->sha = $matches[1];
            }
            if (preg_match('/^Author: (\w+) <(.*)>$/', $line, $matches) > 0) {
                $author = new GitAuthor();
                $author->setName($matches[1]);
                $author->setEmail($matches[2]);
                $this->author = $author;
            }
            if (preg_match('/^Date:   (\w+)$/', $line, $matches) > 0) {
                $this->datetime = new $matches[1];
            }
            if (preg_match('/^    (.*)$/', $line, $matches)) {
                $this->message[] = $matches[1];
            }
        }
    }

    /**
     * Author getter
     *
     * @return GitAuthor
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * datetime getter
     *
     * @return mixed
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * message getter
     *
     * @param bool $trim       output the message as a string
     * @param int  $trimLength the length of the message
     *
     * @return array|string
     */
    public function getMessage($trim = true, $trimLength = 50)
    {
        if ($trim) {
            $msg = implode(' ', $this->message);
            return strlen($msg) > $trimLength ? substr($msg, 0, $trimLength) . '...' : $msg;
        }
        return $this->message;
    }

    /**
     * Sha getter
     *
     * @return mixed
     */
    public function getSha()
    {
        return $this->sha;
    }
}
