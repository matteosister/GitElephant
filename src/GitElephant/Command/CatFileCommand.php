<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Command
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand,
GitElephant\Objects\Object,
GitElephant\Objects\TreeishInterface,
GitElephant\Objects\Tag,
GitElephant\Objects\Branch,
GitElephant\Objects\Commit;

/**
 * cat-file command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class CatFileCommand extends BaseCommand
{
    const GIT_CAT_FILE = 'cat-file';

    /**
     * @return CatFileCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * command to show content of a Object at a given Treeish point
     *
     * @param \GitElephant\Objects\Object              $object  a Object instance
     * @param \GitElephant\Objects\TreeishInterface|string $treeish an object with TreeishInterface interface
     *
     * @return string
     */
    public function content(Object $object, $treeish)
    {
        if ($treeish instanceof TreeishInterface) {
            $sha = $treeish->getSha();
        } else {
            $sha = $treeish;
        }

        $this->clearAll();
        $this->addCommandName(static::GIT_CAT_FILE);
        // pretty format
        $this->addCommandArgument('-p');
        $this->addCommandSubject($sha . ':' . $object->getFullPath());

        return $this->getCommand();
    }

    /**
     * command to show the type of a Object at a given Treeish point
     *
     * @param \GitElephant\Objects\Object       $object  a Object instance
     * @param \GitElephant\Objects\TreeishInterface $treeish an object with TreeishInterface interface
     *
     * @return string
     */
    public function type(Object $object, TreeishInterface $treeish)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_CAT_FILE);
        // pretty format
        $this->addCommandArgument('-p');
        $this->addCommandSubject($treeish->getSha() . ':' . $object->getFullPath());

        return $this->getCommand();
    }

    /**
     * command to show size of a Object at a given Treeish point
     *
     * @param \GitElephant\Objects\Object       $object  a Object instance
     * @param \GitElephant\Objects\TreeishInterface $treeish an object with TreeishInterface interface
     *
     * @return string
     */
    public function size(Object $object, TreeishInterface $treeish)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_CAT_FILE);
        // pretty format
        $this->addCommandArgument('-p');
        $this->addCommandSubject($treeish->getSha() . ':' . $object->getFullPath());

        return $this->getCommand();
    }

    /**
     * Get a reference name
     *
     * @param \GitElephant\Objects\Object $object a Object instance
     * @param string|TreeishInterface         $ref    could be a string (like HEAD, master etc...) or an instance of TreeishInterface
     *
     * @return \GitElephant\Command\could
     * @throws \InvalidArgumentException
     */
    private function getReferenceName(Object $object, $ref)
    {
        $refName = '';
        if (is_string($ref)) {
            return $ref;
        } else {
            if ($ref instanceof TreeishInterface) {
                return $ref->getFullRef();
            } else {
                throw new \InvalidArgumentException(sprintf('ref passed to CatFileCommand should be one of string, Tag, Branch or Commit'));
            }
        }
    }
}
