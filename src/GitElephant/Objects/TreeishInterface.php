<?php

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
