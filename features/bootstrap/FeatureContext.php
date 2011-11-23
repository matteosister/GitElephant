<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use GitElephant\GitBinary,
    GitElephant\Command\Caller,
    GitElephant\Repository,
    GitElephant\Objects\Tree,
    GitElephant\Objects\TreeBranch,
    GitElephant\Objects\Diff;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    private $path;
    /**
     * @var GitElephant\Repository
     * @var GitElephant\Repository
     */
    private $repository;
    private $caller;
    private $tree;
    private $diff;
    private $callResult;
    private $commit;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        spl_autoload_register(function($class)
        {
            $file = __DIR__.'/../../src/'.strtr($class, '\\', '/').'.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
        });
    }

    /**
     * @Given /^I am in a folder$/
     */
    public function iAmInAFolder()
    {
        $tempDir = realpath(sys_get_temp_dir()).'gitelephant_'.md5(uniqid(rand(),1));
        $tempName = tempnam($tempDir, 'gitelephant');
        $this->path = $tempName;
        unlink($this->path);
        mkdir($this->path);
        $binary = new GitBinary('/usr/local/bin/git');
        $this->caller = new Caller($binary, $this->path);
        $this->repository = new Repository($this->path);
    }

    /**
     * @Given /^I init the repository$/
     */
    public function iInitTheRepository()
    {
        $this->repository->init();
    }


    /**
     * @Given /^I add a file named "([^"]*)"$/
     */
    public function iAddAFileNamed($name)
    {
        $filename = $this->path.DIRECTORY_SEPARATOR.$name;
        $handle = fopen($filename, 'w');
        fwrite($handle, 'test content'."\n");
        fclose($handle);
    }

    /**
     * @When /^I add content to the file "([^"]*)" "([^"]*)"$/
     */
    public function iAddContentToTheFile($filename, $content)
    {
        $filename = $this->path.DIRECTORY_SEPARATOR.$filename;
        $handle = fopen($filename, 'a');
        fwrite($handle, "\n".$content."\n");
        fclose($handle);
    }


    /**
     * @Given /^I add a folder named "([^"]*)"$/
     */
    public function iAddAFolderNamed($name)
    {
        mkdir($this->path.DIRECTORY_SEPARATOR.$name);
    }

    /**
     * @When /^I add to the repository "([^"]*)"$/
     */
    public function iAddToTheRepository($what)
    {
        $this->repository->stage($what);
    }


    /**
     * @Given /^I add a file in folder "([^"]*)" "([^"]*)"$/
     */
    public function iAddAFileInFolder($name, $folder)
    {
        $filename = $this->path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$name;
        $handle = fopen($filename, 'w');
        fwrite($handle, 'test content');
        fclose($handle);
    }

    /**
     * @Given /^I stage and commit with message "([^"]*)"$/
     */
    public function iStageAndCommitWithMessage($message)
    {
        $this->repository->commit($message, true);
    }

    /**
     * @Given /^I commit with message "([^"]*)"$/
     */
    public function iCommitWithMessage($message)
    {
        $this->repository->commit($message, false);
    }

    /**
     * @Given /^I start a test repository$/
     */
    public function iStartATestRepository()
    {
        $this->iAmInAFolder('test');
        $this->iInitTheRepository();
        $this->iAddAFileNamed('test-file');
        $this->iStageAndCommitWithMessage('test-message');
    }



    /**
     * @When /^I get tree "([^"]*)"$/
     */
    public function iGetTree($ref)
    {
        $this->tree = $this->repository->getTree($ref);
    }

    /**
     * @Then /^I should get a tree object$/
     */
    public function iShouldGetATreeObject()
    {
        $reflectionClass = new ReflectionClass($this->tree);
        assertEquals('GitElephant\Objects\Tree', $reflectionClass->getName(), "The object is not a Tree object but ".$reflectionClass->getName());
        assertContains('ArrayAccess', $reflectionClass->getInterfaceNames(), "The object do not have the ArrayAccess interface");
        assertContains('Countable', $reflectionClass->getInterfaceNames(), "The object do not have the Countable interface");
        assertContains('Iterator', $reflectionClass->getInterfaceNames(), "The object do not have the Iterator interface");
    }

    /**
     * @Then /^I should get the status$/
     */
    public function iShouldGetTheStatus(PyStringNode $string)
    {
        assertEquals($string->getLines(), $this->repository->getStatus(), 'Status should be an array');
    }

    /**
     * @Then /^The status should contains "([^"]*)"$/
     */
    public function theStatusShouldContains($what)
    {
        assertRegExp(sprintf('/(.*)%s(.*)/', preg_quote($what, '/')), implode('',$this->repository->getStatus()), 'the status do not contains '.$what);
    }

    /**
     * @When /^I create a branch from "([^"]*)" "([^"]*)"$/
     */
    public function iCreateABranchFrom($name, $from)
    {
        $this->repository->createBranch($name, $from);
    }

    /**
     * @Given /^The repository has the method "([^"]*)"$/
     */
    public function theRepositoryHasTheMethod($methodName)
    {
        $reflectionClass = new ReflectionClass($this->repository);
        $methods = $reflectionClass->getMethods();
        $methodsName = array_map(function(ReflectionMethod $method) { return $method->getName(); }, $methods);
        if (!in_array($methodName, $methodsName)) {
            throw new Exception(sprintf("the method %s do not exists on the %s class", $methodName, $reflectionClass->getName()));
        }
    }

    /**
     * @Given /^The repository has the methods$/
     */
    public function theRepositoryHasTheMethods(PyStringNode $string)
    {
        foreach ($string->getLines() as $methodName) {
            $this->theRepositoryHasTheMethod($methodName);
        }
    }


    /**
     * @Given /^I should get an array of objects "([^"]*)"$/
     */
    public function iShouldGetAnArrayOfObjects($objectName)
    {
        if (!is_array($this->callResult)) {
            throw new Exception("The result is not an array");
        }
    }

    /**
     * @When /^I delete the branch "([^"]*)"$/
     */
    public function iDeleteTheBranch($name)
    {
        $this->repository->deleteBranch($name);
    }

    /**
     * @When /^I create a tag "([^"]*)"$/
     */
    public function iCreateATag($name)
    {
        $this->repository->createTag($name);
    }

    /**
     * @Then /^Method should get an array of "([^"]*)" "([^"]*)"$/
     */
    public function methodShouldGetAnArrayOf($methodName, $objectsName)
    {
        $result = call_user_func(array($this->repository, $methodName));
        foreach ($result as $single) {
            $reflectionClass = new ReflectionClass($single);
            if ($reflectionClass->getName() !== $objectsName) {
                throw new Exception(sprintf("not all objects in the array are %s, at least one is %s", $objectsName, $reflectionClass->getName()));
            }
        }
    }

    /**
     * @Then /^Method should get a count of "([^"]*)" (\d+)$/
     */
    public function methodShouldGetACountOf($methodName, $count)
    {
        $result = call_user_func(array($this->repository, $methodName));
        if (!is_array($result)) {
            assertInstanceOf('Countable', $result, 'The result is not a Countable object');
        }
        assertEquals($count, count($result), sprintf('The result is not %s but %s', $count, count($result)));
    }

    /**
     * @Given /^Tree should get a count of (\d+)$/
     */
    public function treeShouldGetACountOf($count)
    {
        assertEquals($count, count($this->tree), sprintf('Tree count is not %s but %s', $count, count($this->tree)));
    }

    /**
     * @When /^I delete a tag "([^"]*)"$/
     */
    public function iDeleteATag($name)
    {
        $this->repository->deleteTag($name);
    }

    /**
     * @Then /^Method should get an object "([^"]*)" "([^"]*)"$/
     */
    public function methodShouldGetAnObject($methodName, $objectName)
    {
        $result = call_user_func(array($this->repository, $methodName));
        $reflectionClass = new ReflectionClass($result);
        assertEquals($objectName, $reflectionClass->getName(), sprintf("method return %s instead of %s", $reflectionClass->getName(),$objectName));
    }

    /**
     * @Given /^Method should get an object with attribute "([^"]*)" "([^"]*)" "([^"]*)"$/
     */
    public function methodShouldGetAnObjectWithAttribute($methodName, $attributeMethod, $expected)
    {
        $obj = call_user_func(array($this->repository, $methodName));
        $result = call_user_func(array($obj, $attributeMethod));
        assertEquals($expected, $result, sprintf("Method %s return %s instead of %s", $attributeMethod, $result, $expected));
    }

    /**
     * @When /^I get tree for a branch object "([^"]*)"$/
     */
    public function iGetTreeForABranchObject($branchName)
    {
        $branch = $this->repository->getBranch($branchName);
        $this->tree = $this->repository->getTree($branch);
    }

    /**
     * @When /^I get tree for the main branch$/
     */
    public function iGetTreeForTheMainBranch()
    {
        $this->iGetTreeForABranchObject($this->repository->getMainBranch()->getName());
    }


    /**
     * @Given /^I get tree for a tag object "([^"]*)"$/
     */
    public function iGetTreeForATagObject($tagName)
    {
        $tag = $this->repository->getTag($tagName);
        $this->tree = $this->repository->getTree($tag);
    }

    /**
     * @Given /^I checkout "([^"]*)"$/
     */
    public function iCheckout($what)
    {
        $this->repository->checkout($what);
    }

    /**
     * @When /^I checkout to main branch$/
     */
    public function iCheckoutToMainBranch()
    {
        $this->repository->checkout($this->repository->getMainBranch());
    }

    /**
     * @Given /^I call getCommitDiff with last commit$/
     */
    public function iCallGetcommitdiffWithLastCommit()
    {
        $lastCommit = $this->repository->getCommit('HEAD');
        $this->diff = $this->repository->getCommitDiff($lastCommit);
    }


    /**
     * @Then /^Diff should get a count of (\d+)$/
     */
    public function diffShouldGetACountOf($count)
    {
        assertInstanceOf('Countable', $this->diff, 'The result is not a Countable object');
        assertEquals($count, count($this->diff), sprintf('The result is not %s but %s', $count, count($this->diff)));
    }

    /**
     * @When /^I call getCommit$/
     */
    public function iCallGetcommit()
    {
        $this->commit = $this->repository->getCommit();
    }

    /**
     * @Then /^The commit should have the methods$/
     */
    public function theCommitShouldHaveTheMethods(PyStringNode $methods)
    {
        $reflectionClass = new ReflectionClass($this->commit);
        foreach ($methods->getLines() as $method) {
            assertInstanceOf('ReflectionMethod', $reflectionClass->getMethod($method), sprintf('The Commit class do not have a %s method', $method));
        }
    }

    /**
     * @Given /^the commit should have not null values$/
     */
    public function theCommitShouldHaveNotNullValues(PyStringNode $methods)
    {
        foreach($methods->getLines() as $method) {
            assertNotNull(call_user_func(array($this->commit, $method)), sprintf('The method %s return null', $method));
        }
    }

    /**
     * @Then /^Commit message should not be an empty array$/
     */
    public function commitMessageShouldNotBeAnEmptyArray()
    {
        assertNotEquals(array(), $this->commit->getMessage(), 'Message is an empty array');
    }

    /**
     * @Given /^Diff should have a DiffObject named "([^"]*)"$/
     */
    public function diffShouldHaveADiffobjectNamed($destPath)
    {
        $diffObject = $this->diff[0];
        assertEquals($destPath, $diffObject->getDestPath(), sprintf('diff do not contains %s but %s', $destPath, $diffObject->getDestPath()));
    }

    /**
     * @Given /^DiffObject should get a count of (\d+)$/
     */
    public function diffobjectShouldGetACountOf($count)
    {
        assertInstanceOf('Countable', $this->diff[0], 'The result is not a Countable object');
        assertEquals($count, count($this->diff[0]), sprintf('The result is not %s but %s', $count, count($this->diff[0])));
    }


}
