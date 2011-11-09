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

use GitWrapper\Repository;
use GitWrapper\GitBinary;
use GitWrapper\Command\Caller;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $caller;
    protected $repository;

    public function __construct()
    {
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.md5(uniqid());
        mkdir($this->path);
        $this->binary = new GitBinary('/usr/local/bin/git');
        $this->caller = new Caller($this->binary, $this->path);
        $this->repository = new Repository($this->path);
    }
}
