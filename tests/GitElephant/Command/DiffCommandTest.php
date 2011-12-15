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

use GitElephant\Command\DiffCommand;
use GitElephant\TestCase;

/**
 * DiffCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffCommandTest extends TestCase
{
    /**
     * @var \GitElephant\Command\DiffCommand;
     */
    private $diffCommand;

    /**
     * set up
     */
    public function setUp()
    {
        $this->diffCommand = new DiffCommand();
    }

    /**
     * diff test
     */
    public function testDiff()
    {
        $this->assertEquals(DiffCommand::DIFF_COMMAND." '--full-index' '--no-color' '--dst-prefix=DST/' '--src-prefix=SRC/'", $this->diffCommand->diff());
        $this->assertEquals(DiffCommand::DIFF_COMMAND." '--full-index' '--no-color' '--dst-prefix=DST/' '--src-prefix=SRC/' HEAD", $this->diffCommand->diff('HEAD'));
        $this->assertEquals(DiffCommand::DIFF_COMMAND." '--full-index' '--no-color' '--dst-prefix=DST/' '--src-prefix=SRC/' HEAD HEAD~1", $this->diffCommand->diff('HEAD', 'HEAD~1'));
        $this->assertEquals(DiffCommand::DIFF_COMMAND." '--full-index' '--no-color' '--dst-prefix=DST/' '--src-prefix=SRC/' HEAD HEAD~1 -- foo", $this->diffCommand->diff('HEAD', 'HEAD~1', 'foo'));
    }
}
