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

use GitElephant\Command\BaseCommand,
GitElephant\Objects\TreeObject,
GitElephant\Objects\TreeishInterface;

/**
 * cat-file command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class CatFileCommand extends BaseCommand
{
    const GIT_CAT_FILE = 'cat-file';
    /**
     * command to show content of a TreeObject at a given Treeish point
     *
     * @param \GitElephant\Objects\TreeObject       $object  a TreeObject instance
     * @param \GitElephant\Objects\TreeishInterface $treeish an object with TreeishInterface interface
     */
    public function content(TreeObject $object, TreeishInterface $treeish)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_CAT_FILE);
        // pretty format
        $this->addCommandArgument('-p');
        $this->addCommandSubject($treeish->getSha().':'.$object->getFullPath());
        return $this->getCommand();
    }

    /**
     * command to show the type of a TreeObject at a given Treeish point
     *
     * @param \GitElephant\Objects\TreeObject       $object  a TreeObject instance
     * @param \GitElephant\Objects\TreeishInterface $treeish an object with TreeishInterface interface
     */
    public function type(TreeObject $object, TreeishInterface $treeish)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_CAT_FILE);
        // pretty format
        $this->addCommandArgument('-p');
        $this->addCommandSubject($treeish->getSha().':'.$object->getFullPath());
        return $this->getCommand();
    }

    /**
     * command to show size of a TreeObject at a given Treeish point
     *
     * @param \GitElephant\Objects\TreeObject       $object  a TreeObject instance
     * @param \GitElephant\Objects\TreeishInterface $treeish an object with TreeishInterface interface
     */
    public function size(TreeObject $object, TreeishInterface $treeish)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_CAT_FILE);
        // pretty format
        $this->addCommandArgument('-p');
        $this->addCommandSubject($treeish->getSha().':'.$object->getFullPath());
        return $this->getCommand();
    }

    /**
     *
     *
     * @param \GitElephant\Objects\TreeObject $object a TreeObject instance
     * @param                                 $ref    could be a string (like HEAD, master etc...) or an instance of: TreeTag, TreeBranch, Commit
     *
     * @throws \InvalidArgumentException
     */
    private function getReferenceName(TreeObject $object, $ref)
    {
        $refName = '';
        if (is_string($ref)) {
            $refName = $ref;
        } else {
            if ($ref instanceof TreeTag) {

            } else {
                if ($ref instanceof TreeBranch) {

                } else {
                    if ($ref instanceof Commit) {

                    } else {
                        throw new \InvalidArgumentException(sprintf('ref passed to CatFileCommand should be one of string, TreeTag, TreeBranch or Commit'));
                    }
                }
            }
        }
    }
}
