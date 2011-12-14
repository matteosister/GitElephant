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

namespace GitElephant\Command;

use GitElephant\TestCase;
use GitElephant\Command\Caller;
use GitElephant\GitBinary;

/**
 * CallerTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class CallerTest extends TestCase
{
    protected $caller;

    public function setUp()
    {
        $binary = new GitBinary();
        $this->caller = new Caller($binary);
    }
}
