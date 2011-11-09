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

namespace GitWrapper;

use GitWrapper\Command\Tree\Tree;
use GitWrapper\Command\Init;

/**
 * RepositoryTest
 *
 * Repository Test Class
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class RepositoryTest extends TestCase
{
    public function testInit()
    {
        $this->repository->init();
        $this->assertEquals(array('.git'), $this->caller->execute('ls')->getResult(), 'equal');
        //$this->assertTrue($this->caller->execute('ls') == '.git', 'The folder contains a .git subfolder');
    }
}
