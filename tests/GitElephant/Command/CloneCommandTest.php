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

use \GitElephant\Command\CloneCommand;
use \GitElephant\TestCase;

/**
 * CloneCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class CloneCommandTest extends TestCase
{
    /**
     * @var string
     */
    private $binaryVersion;

    /**
     * set up
     */
    public function setUp(): void
    {
        $this->initRepository();
        $this->binaryVersion = exec('git --version | cut -d " " -f 3');
    }

    /**
     * set up
     */
    public function testCloneUrl()
    {
        $cc = CloneCommand::getInstance($this->getRepository());
        $this->assertEquals(
            "clone 'git://github.com/matteosister/GitElephant.git'",
            $cc->cloneUrl('git://github.com/matteosister/GitElephant.git')
        );
        $this->assertEquals(
            "clone 'git://github.com/matteosister/GitElephant.git' 'test'",
            $cc->cloneUrl('git://github.com/matteosister/GitElephant.git', 'test')
        );

        if (version_compare($this->binaryVersion, '1.8.3.1', '<')) {
            // Will fail if tested on git version 1.8.3.0 or lower
            $this->expectException(\RuntimeException::class);
            $cc->cloneUrl('git://github.com/matteosister/GitElephant.git', 'test', 'master');
        } else {
            $this->assertEquals(
                "clone '--branch=master' 'git://github.com/matteosister/GitElephant.git' 'test'",
                $cc->cloneUrl('git://github.com/matteosister/GitElephant.git', 'test', 'master')
            );
        }

        $this->assertEquals(
            "clone '--depth=1' 'git://github.com/matteosister/GitElephant.git' 'test'",
            $cc->cloneUrl('git://github.com/matteosister/GitElephant.git', 'test', null, 1)
        );

        // Output depends on git version used
        if (version_compare($this->binaryVersion, '2.9.0', '<')) {
            $expected = "clone '--depth=1' '--recursive' 'git://github.com/matteosister/GitElephant.git' 'test'";
        } else {
            $expected = "clone '--depth=1' '--shallow-submodules' '--recursive' 'git://github.com/matteosister/GitElephant.git' 'test'";
        }
        $this->assertEquals(
            $expected,
            $cc->cloneUrl('git://github.com/matteosister/GitElephant.git', 'test', null, 1, true)
        );
    }
}
