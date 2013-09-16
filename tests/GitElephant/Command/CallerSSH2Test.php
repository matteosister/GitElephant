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
use GitElephant\Command\MainCommand;

/**
 * CallerTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class CallerSSH2Test extends TestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->initRepository();
    }

    /**
     * @covers GitElephant\Command\Caller::__construct
     */
    public function testConstructor()
    {
        $caller = new CallerSSH2('localhost');
        $caller->setUserPasswordAuthentication('matteo', 'nmdcdnv');
        //$this->assertNotEmpty($caller->execute('--version'));
        $caller->execute('--version');
    }
}
