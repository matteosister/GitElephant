<?php

use GitElephant\Repository;
use Sami\Version\GitVersionCollection;

$dir = __DIR__.'/src';

$versions = GitVersionCollection::create($dir);
$versions->add('master', 'master branch');
foreach (Repository::open('.')->getTags() as $tag) {
    $versions->addFromTags($tag->getName());
}

return new Sami\Sami($dir, array(
    'build_dir' => 'build/%version%',
    'cache_dir' => 'cache/%version%',
    'title' => 'GitElephant API',
    'default_opened_level' => 2,
    'versions' => $versions
));