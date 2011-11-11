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
use \GitElephant\Command\LsTreeCommand;


/**
 * NestedTree
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class NestedTree
{
    private $tree;
    private $caller;
    private $lsTreeCommand;

    public function __construct(Caller $caller)
    {
        $this->caller = $caller;
        $this->lsTreeCommand = new LsTreeCommand();

        $this->parse();
    }

    private function parse()
    {
        $folderLines = $this->caller->execute($this->lsTreeCommand->listFolders())->getOutputLines();
        if (count($folderLines) > 0) {
            foreach($folderLines as $folderLine) {
                $this->tree[] = $this->parseLine($folderLine);
            }
        }
        var_dump($this->tree);

        foreach($this->tree as $baseFolder) {
            $folderLines = $this->caller->execute($this->lsTreeCommand->listFolders($baseFolder[2]))->getOutputLines();
            var_dump(count($folderLines));
        }


    }


    private function parseLine($line)
    {
        preg_match('/(\d+)\ (\w+)\ ([a-z0-9]+)\t(.*)/', $line, $matches);
        $permissions = $matches[1];
        $type = $matches[2] == 'tree' ? TreeObject::TYPE_TREE : TreeObject::TYPE_BLOB;
        $sha = $matches[3];
        $name = $matches[4];
        return array($permissions, $type, $sha, $name);
    }
}
