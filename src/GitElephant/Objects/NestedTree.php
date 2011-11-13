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
use GitElephant\Objects\NestedTreeBlob;
use GitElephant\Objects\NestedTreeTree;
use GitElephant\Objects\NestedTreeObject;


/**
 * NestedTree
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class NestedTree
{
    protected $blobs = array();
    protected $trees = array();
    protected $caller;
    protected $lsTreeCommand;

    public function __construct(Caller $caller)
    {
        $this->caller = $caller;
        $this->lsTreeCommand = new LsTreeCommand();
        $this->parse();
    }

    protected function parse()
    {
        $command = $this->lsTreeCommand->listAll();
        $baseNodes = $this->caller->execute($command)->getOutputLines();
        foreach($baseNodes as $nodeLine) {
            $arrLine = NestedTreeObject::parseLine($nodeLine);
            switch ($arrLine['type']) {
                case NestedTreeObject::TYPE_BLOB:
                    $this->blobs[] = new NestedTreeBlob($nodeLine);
                    break;
                case NestedTreeObject::TYPE_TREE:
                    $this->trees[] = new NestedTreeTree($this->caller, $nodeLine);
                    break;
            }
        }
    }

    public function getBlobs()
    {
        return $this->blobs;
    }

    public function getTrees()
    {
        return $this->trees;
    }
}
