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

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use GitElephant\GitBinary,
    GitElephant\Repository,
    GitElephant\Command\Caller,
    GitElephant\Objects\Diff\Diff,
    GitElephant\Objects\Diff\DiffObject,
    GitElephant\Objects\Diff\DiffChunk,
    GitElephant\Objects\Diff\DiffChunkLine;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * DiffContext
 *
 * Diff Behat Context
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffContext extends BehatContext
{
    private $commit_number = 0;
    private $path;
    private $caller;
    /**
     * @var \GitElephant\Repository
     */
    private $repository;
    /**
     * @var \GitElephant\Objects\Diff\Diff
     */
    private $diff;
    /**
     * @var array
     */
    private $diffObjects = array();
    /**
     * @var array
     */
    private $diffChunks = array();
    /**
     * @var array
     */
    private $diffChunkLines = array();

    /**
     * @Given /^I start a repository for diff$/
     */
    public function iStartARepositoryForDiff()
    {
        $tempDir = realpath(sys_get_temp_dir()).'gitelephant_'.md5(uniqid(rand(),1));
        $tempName = tempnam($tempDir, 'gitelephant');
        $this->path = $tempName;
        unlink($this->path);
        mkdir($this->path);
        $binary = new GitBinary('/usr/local/bin/git');
        $this->caller = new Caller($binary, $this->path);
        $this->repository = new Repository($this->path);
        $this->repository->init();
    }

    /**
     * @Given /^I add a file named "([^"]*)" to the repository with content$/
     */
    public function iAddAFileNamedToTheRepositoryWithContent($name, PyStringNode $string)
    {
        $filename = $this->path.DIRECTORY_SEPARATOR.$name;
        $handle = fopen($filename, 'w');
        foreach($string->getLines() as $line)
        {
            fwrite($handle, $line.PHP_EOL);
        }
        fclose($handle);
    }

    /**
     * @Then /^I add a file named "([^"]*)" to the repository without content$/
     */
    public function iAddAFileNamedToTheRepositoryWithoutContent($name)
    {
        $filename = $this->path.DIRECTORY_SEPARATOR.$name;
        touch($filename);
    }


    /**
     * @Given /^I stage and commit$/
     */
    public function iStageAndCommit()
    {
        $this->repository->stage();
        $this->repository->commit('commit '.++$this->commit_number, true);
        $this->diff = $this->repository->getDiff($this->repository->getCommit());
    }

    /**
     * @Given /^I rename "([^"]*)" to "([^"]*)"$/
     */
    public function iRenameTo($from, $to)
    {
        rename($this->path . '/' . $from, $this->path . '/' . $to);
    }

    /**
     * @Then /^the last commit should be root$/
     */
    public function theLastCommitShouldBeRoot()
    {
        assertTrue($this->repository->getCommit()->isRoot());
    }


    /**
     * @Then /^the diff should have "([^"]*)" object of mode "([^"]*)"$/
     */
    public function theDiffShouldHaveObjectOfType($num, $mode)
    {
        $this->diffObjects = array();
        $count = 0;
        foreach($this->diff as $diffObject) {
            $this->diffObjects[] = $diffObject;
            if ($diffObject->getMode() == $mode) {
                $count++;
            }
        }
        assertEquals((int)$num, $count);
    }

    /**
     * @Given /^the diffObject in position "([^"]*)" should have "([^"]*)" diffChunk$/
     */
    public function theDiffobjectInPositionShouldHaveDiffchunk($num, $num_chunks)
    {
        $this->diffChunks = array();
        $diffObject = $this->diffObjects[$num-1];
        assertCount((int)$num_chunks, $diffObject);
        foreach($diffObject as $diffChunk) {
            $this->diffChunks[] = $diffChunk;
        }
    }

    /**
     * @Given /^the diffObject in position "([^"]*)" should be a rename from "([^"]*)" to "([^"]*)"$/
     */
    public function theDiffobjectInPositionShouldBeARenameFromTo($num, $from, $to)
    {
        /* @var $diffObject \GitElephant\Objects\Diff\DiffObject */
        $diffObject = $diffObject = $this->diffObjects[$num-1];

        assertTrue($diffObject->hasPathChanged());
        assertEquals($from, $diffObject->getOriginalPath());
        assertEquals($to, $diffObject->getDestinationPath());
    }

    /**
     * @Given /^the diffObject in position "([^"]*)" should have a similarity of "([^"]*)" percent$/
     */
    public function theDiffobjectInPositionShouldHaveASimilarityOfPercent($num, $percent)
    {
        /* @var $diffObject \GitElephant\Objects\Diff\DiffObject */
        $diffObject = $diffObject = $this->diffObjects[$num-1];

        assertEquals($percent, $diffObject->getSimilarityIndex());
    }

    /**
     * @Given /^the diffChunk in position "([^"]*)" should have "([^"]*)" diffChunkLine$/
     */
    public function theDiffchunkInPositionShouldHaveDiffchunklines($pos, $num)
    {
        $this->diffChunkLines = array();
        $diffChunk = $this->diffChunks[$pos-1];
        assertCount((int)$num, $diffChunk);
        foreach($diffChunk as $diffChunkLine) {
            $this->diffChunkLines[] = $diffChunkLine;
        }
    }

    /**
     * @Given /^the diffChunkLine in position "([^"]*)" should be "([^"]*)"$/
     */
    public function theDiffchunklineInPositionShouldBe($pos, $type)
    {
        $diffChunkLine = $this->diffChunkLines[$pos-1];
        assertInstanceOf($type, $diffChunkLine);
    }

    /**
     * @Given /^the diffChunkLine in position "([^"]*)" should have line number (\d+)$/
     */
    public function theDiffchunklineInPositionShouldHaveLineNumber($pos, $num)
    {
        $diffChunkLine = $this->diffChunkLines[$pos-1];
        assertEquals($diffChunkLine->getNumber(), (int)$num);
    }
}
