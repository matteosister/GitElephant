<?php
/*
 * This file is part of the GitElephant package.
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
use GitElephant\Objects\NestedTreeObject;

/**
 * NestedTreeTree
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class NestedTreeTree extends NestedTreeObject
{
    public function __construct(Caller $caller, $line)
    {
        parent::__construct();
        $this->caller = $caller;
        $this->setAttributes($line);
        $this->parse();
    }

    protected function parse()
    {
        $command = $this->lsTreeCommand->listAll($this->getSha());
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

    protected function hasSubTrees()
    {
        return count($this->caller->execute($this->lsTreeCommand->listTrees($this->getSha()))->getOutputLines()) > 0;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getSha()
    {
        return $this->sha;
    }

    public function getType()
    {
        return $this->type;
    }
}
