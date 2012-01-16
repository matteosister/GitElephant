<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects
 *
 * Just for fun...
 */

namespace GitElephant\Objects;

/**
 * TreeishInterface
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

interface TreeishInterface
{
    /**
     * get the unique sha for the treeish object
     *
     * @abstract
     */
    public function getSha();

    /**
     * toString magic method, should return the sha of the treeish
     *
     * @abstract
     */
    public function __toString();
}
