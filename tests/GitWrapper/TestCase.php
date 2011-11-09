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
use Symfony\Component\Finder\Finder;

class TestCase extends \PHPUnit_Framework_TestCase
{
    private $caller;
    private $repository;
    private $path;
    private $finder;

    /**
     * @return Repository
     */
    protected function getRepository()
    {
        if ($this->repository == null) {
            $this->initRepository();
        }
        return $this->repository;
    }

    /**
     * @return Caller
     */
    protected function getCaller()
    {
        if ($this->caller == null) {
            $this->initRepository();
        }
        return $this->caller;
    }

    protected function initRepository()
    {
        if ($this->repository == null) {
            $tempDir = realpath(sys_get_temp_dir()).'gitwrapper_'.md5(uniqid(rand(),1));
            $tempName = tempnam($tempDir, 'gitwrapper');
            $this->path = $tempName;
            unlink($this->path);
            mkdir($this->path);
            $binary = new GitBinary('/usr/local/bin/git');
            $this->caller = new Caller($binary, $this->path);
            $this->repository = new Repository($this->path);
        }
    }
}
