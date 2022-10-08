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

/**
 * DiffTreeCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffTreeCommandTest extends TestCase
{
    /**
     * set up
     */
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('foo');
        $this->getRepository()->commit('first commit', true);
    }

    /**
     * set up
     */
    public function testRootDiff(): void
    {
        $dtc = DiffTreeCommand::getInstance();
        $commit = $this->getRepository()->getCommit();
        $command = $dtc->rootDiff($commit);
        $this->assertEquals(
            sprintf("diff-tree '--cc' '--root' '--dst-prefix=DST/' '--src-prefix=SRC/' '%s'", $commit),
            $command
        );
        $this->addFile('test');
        $this->getRepository()->commit('test commit', true);
        $this->expectException('InvalidArgumentException');
        $this->fail($dtc->rootDiff($this->getRepository()->getCommit()));
    }
}
