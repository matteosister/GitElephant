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

/**
 * TreeishInterface
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

interface TreeishInterface
{
    /**
     * @abstract
     *
     * get the unique sha for the treeish object
     */
    public function getSha();
}
