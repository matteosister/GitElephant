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

namespace GitElephant;

use GitElephant\Repository;
use GitElephant\GitBinary;
use GitElephant\Command\Caller;
use Symfony\Component\Finder\Finder;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $caller;
    protected $repository;
    protected $path;
    protected $finder;

    /**
     * @return \GitElephant\Repository
     */
    protected function getRepository()
    {
        if ($this->repository == null) {
            $this->initRepository();
        }
        return $this->repository;
    }

    /**
     * @return \GitElephant\Command\Caller
     */
    protected function getCaller()
    {
        if ($this->caller == null) {
            $this->initRepository();
        }
        return $this->caller;
    }

    /**
     * @return void
     */
    protected function initRepository()
    {
        if ($this->repository == null) {
            $tempDir = realpath(sys_get_temp_dir()).'gitelephant_'.md5(uniqid(rand(),1));
            $tempName = tempnam($tempDir, 'gitelephant');
            $this->path = $tempName;
            unlink($this->path);
            mkdir($this->path);
            $binary = new GitBinary();
            $this->caller = new Caller($binary, $this->path);
            $this->repository = new Repository($this->path);
        }
    }

    /**
     * @param string $name
     * @param string|null $folder
     * @return void
     */
    protected function addFile($name, $folder = null, $content = null)
    {
        $filename = $folder == null ?
                $this->path.DIRECTORY_SEPARATOR.$name :
                $this->path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$name;
        $handle = fopen($filename, 'w');
        $file_content = $content == null ? 'test content' : $content;
        fwrite($handle, $file_content);
        fclose($handle);
    }

    /**
     * @param string $name
     * @return void
     */
    protected function addFolder($name)
    {
        mkdir($this->path.DIRECTORY_SEPARATOR.$name);
    }

    protected function mockCaller($command, $output) {
        $mock = $this->getMock('GitElephant\Command\CallerInterface');
        $mock->expects($this->any())
            ->method('execute')
            ->with($this->equalTo($command))
            ->will($this->returnValue($mock));
        $mock->expects($this->any())
            ->method('getOutputLines')
            ->will($this->returnValue($output));
        return $mock;
    }
}
