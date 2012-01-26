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
     * Origin line number
     *
     * @var int
     */
    protected $originNumber;

    /**
     * Destination line number
     *
     * @var int
     */
    protected $destNumber;

    /**
     * Class constructor
     *
     * @param int    $number  line number
     * @param string $content line content
     */
    public function __construct($originNumber, $destinationNumber, $content)
    {
        $this->setOriginNumber($originNumber);
        $this->setDestNumber($destinationNumber);
        $this->setContent(trim($content));
        $this->setType(self::UNCHANGED);
    }

    /**
     * Set origin line number
     *
     * @param int $number line number
     */
    public function setOriginNumber($number)
    {
        $this->originNumber = $number;
    }

    /**
     * Get origin line number
     *
     * @return int
     */
    public function getOriginNumber()
    {
        return $this->originNumber;
    }

    /**
     * Set destination line number
     *
     * @param int $number line number
     */
    public function setDestNumber($number)
    {
        $this->destNumber = $number;
    }

    /**
     * Get destination line number
     *
     * @return int
     */
    public function getDestNumber()
    {
        return $this->destNumber;
    }
}
