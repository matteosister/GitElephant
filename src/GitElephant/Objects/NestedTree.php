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
    private $treeNodes = array();
    private $blobNodes = array();
    protected $caller;
    protected $lsTreeCommand;

    public function __construct(Caller $caller)
    {
        $this->caller = $caller;
        $this->lsTreeCommand = new LsTreeCommand();
        $this->tree = $this->parseSubNodes();
        var_dump($this->tree);
    }

    public function getFilesIn($sha = null)
    {
        return $this->blobNodes[$sha];
    }

    private function parseSubNodes(NestedTreeNode $node = null)
    {
        $tree = array();
        $this->blobNodes['HEAD'] = $this->getBlobs();
        $command = $node == null ? $this->lsTreeCommand->listTrees() : $this->lsTreeCommand->listTrees($node->getSha());
        $baseFolders = $this->caller->execute($command)->getOutputLines();
        foreach($baseFolders as $baseFolder) {
            $node = new NestedTreeNode($baseFolder, $node);
            $this->blobNodes[$node->getSha()] = $this->getBlobs($node);
            if ($this->hasSubTrees($node)) {
                $tree[$node->getSha()] = $this->parseSubNodes($node);
            } else {
                $tree[$node->getSha()] = null;
                $this->treeNodes[$node->getSha()] = $this->getBlobs($node);
            }
        }
        return $tree;
    }

    private function hasSubTrees(NestedTreeNode $node)
    {
        $folderLines = $this->caller->execute($this->lsTreeCommand->listTrees($node->getSha()))->getOutputLines();
        return count($folderLines) > 0;
    }
    private function getBlobs(NestedTreeNode $node = null)
    {
        $blobs = array();
        $command = $node == null ? $this->lsTreeCommand->listAll('HEAD') : $this->lsTreeCommand->listAll($node->getSha());
        $folderLines = $this->caller->execute($command)->getOutputLines();
        foreach($folderLines as $line) {
            $theNode = new NestedTreeNode($line, $node);
            if ($theNode->getType() == NestedTreeNode::TYPE_BLOB) $blobs[] = $theNode;
        }
        return $blobs;
    }
}
