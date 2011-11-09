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

namespace GitWrapper\Objects;

use GitWrapper\TestCase;

/**
 * TreeTest
 *
 * Tree class tests
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeTest extends TestCase
{
    public function setUp()
    {
        $this->initRepository();
    }
    public function testLsTree()
    {
        $this->assertTrue(true, 'true is true');
    }
}
