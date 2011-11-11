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

namespace GitElephant\Objects;

use GitElephant\Command\Caller;
use GitElephant\Command\LsTreeCommand;
use GitElephant\Objects\NestedTreeNode;


/**
 * NestedTree
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class NestedTree
{
    private $tree = array();
    protected $caller;
    protected $lsTreeCommand;

    public function __construct(Caller $caller)
    {
        $this->caller = $caller;
        $this->lsTreeCommand = new LsTreeCommand();
        $this->tree = $this->parseSubNodes();
    }

    private function parseSubNodes(NestedTreeNode $node = null)
    {
        $tree = array();
        $command = $node == null ? $this->lsTreeCommand->listTrees() : $this->lsTreeCommand->listTrees($node->getSha());
        $baseFolders = $this->caller->execute($command)->getOutputLines();
        foreach($baseFolders as $baseFolder) {
            $node = new NestedTreeNode($baseFolder);
            if ($this->hasSubTrees($node)) {
                $tree[$node->getSha()] = $this->parseSubNodes($node);
            } else {
                $tree[$node->getSha()]['blobs'] = $this->getBlobs($node);
            }
        }
        return $tree;
    }

    private function hasSubTrees(NestedTreeNode $node)
    {
        $folderLines = $this->caller->execute($this->lsTreeCommand->listTrees($node->getSha()))->getOutputLines();
        return count($folderLines) > 0;
    }
    private function getBlobs(NestedTreeNode $node)
    {
        $blobs = array();
        $folderLines = $this->caller->execute($this->lsTreeCommand->listAll($node->getSha()))->getOutputLines();
        foreach($folderLines as $line) {
            $node = new NestedTreeNode($line);
            if ($node->getType() == NestedTreeNode::TYPE_BLOB) $blobs[] = $node;
        }
        return $blobs;
    }
}
