<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects\Diff
 *
 * Just for fun...
 */

namespace GitElephant\Objects\Diff;

use GitElephant\Objects\Diff\DiffChunkLine;

/**
 * DiffChunkLine unchanged
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffChunkLineUnchanged extends DiffChunkLine
{
    /**
     * Class constructor
     *
     * @param int    $number  line number
     * @param string $content line content
     */
    public function __construct($number, $content)
    {
        $this->setNumber($number);
        $this->setContent($content);
        $this->setType(self::UNCHANGED);
    }
}
