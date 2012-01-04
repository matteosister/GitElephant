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
    GitElephant\Command\Caller;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * DiffContext
 *
 * @todo   : description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffContext extends BehatContext
{
    private $path;
    private $caller;
    /**
     * @var GitElephant\Repository
     */
    private $repository;
    /**
     * @var GitElephant\Objects\Diff\Diff
     */
    private $diff;

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
        foreach($string as $line)
        {
            fwrite($handle, 'test content'."\n");
        }
        fclose($handle);
        $this->repository->commit('commit message', true);
        $this->diff = $this->repository->getDiff($this->repository->getCommit());
    }

}
