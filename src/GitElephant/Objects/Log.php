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
 * Log
 *
 * @todo   : description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Log
{
    private $sha;
    private $author;
    private $message;
    private $datetime;

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

    public function getAuthor()
    {
        return $this->author;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function getMessage($trim = true, $trim_length = 50)
    {
        if ($trim) {
            $msg = implode(' ', $this->message);
            return strlen($msg) > $trim_length ? substr($msg, 0, $trim_length) . '...' : $msg;
        }
        return $this->message;
    }

    public function getSha()
    {
        return $this->sha;
    }
}
