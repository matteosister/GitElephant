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
use GitElephant\Command\LsTreeCommand;
use GitElephant\Objects\NestedTreeBlob;
use GitElephant\NestedTreeTree;

/**
 * NestedTreeObject
 *
 * @todo: description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class NestedTreeObject
{
    const TYPE_BLOB = 'blob';
    const TYPE_TREE = 'tree';

    protected $caller;
    protected $lsTreeCommand;

    protected $line;
    protected $parent;
    protected $permissions;
    protected $type;
    protected $sha;
    protected $name;

    protected $blobs = array();
    protected $trees = array();

    protected function __construct()
    {
        $this->lsTreeCommand = new LsTreeCommand();
    }

    public static function parseLine($line)
    {
        preg_match('/(\d+)\ (\w+)\ ([a-z0-9]+)\t(.*)/', $line, $matches);
        $permissions = $matches[1];
        $type = $matches[2] == NestedTreeObject::TYPE_TREE ? NestedTreeObject::TYPE_TREE : NestedTreeObject::TYPE_BLOB;
        $sha = $matches[3];
        $name = $matches[4];
        return array(
            'permissions' => $permissions,
            'type' => $type,
            'sha' => $sha,
            'name' => $name
        );
    }

    public function setAttributes($line)
    {
        $this->line = $line;
        $arrLine = self::parseLine($line);
        $this->permissions = $arrLine['permissions'];
        $this->type = $arrLine['type'];
        $this->sha = $arrLine['sha'];
        $this->name = $arrLine['name'];
    }
}
