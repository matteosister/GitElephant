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

namespace GitElephant\Objects;


/**
 * Represent a single portion of a file changed in a diff
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffChunk
{
    private $origin_start_line;
    private $origin_end_line;
    private $dest_start_line;
    private $dest_end_line;
    private $unchanged;
    private $added;
    private $deleted;

    public function __construct($lines)
    {
        $this->unchanged = array();
        $this->added = array();
        $this->deleted = array();

        $this->getLinesNumbers($lines[0]);
        $this->parseLines(array_slice($lines, 1));
    }

    private function parseLines($lines)
    {
        foreach ($lines as $line) {
            if (preg_match('/ (.*)/', $line)) {
                $this->unchanged[] = ltrim($line);
            } else if (preg_match('/\+(.*)/', $line)) {
                $this->added[] = preg_replace('/\+(.*)/', '$1', $line);
            } else if (preg_match('/-(.*)/', $line)) {
                $this->deleted[] = preg_replace('/-(.*)/', '$1', $line);
            } else {
                throw new \Exception(sprintf('GitElephant was unable to parse the line %s', $line));
            }
        }
    }

    private function getLinesNumbers($line) {
        $matches = array();
        preg_match('/@@ -(\d+),(\d+) \+(\d+),(\d+)?(.*)/', $line, $matches);
        $this->origin_start_line = (int)$matches[1];
        $this->origin_end_line = $matches[1] + $matches[2];
        $this->dest_start_line = (int)$matches[3];
        $this->dest_end_line = $matches[3] + $matches[4];
    }
}
