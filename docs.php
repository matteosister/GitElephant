<?php

use GitElephant\Repository;
use Sami\Version\GitVersionCollection;

$dir = __DIR__.'/src';

$versions = GitVersionCollection::create($dir);
foreach (Repository::open('.')->getTags() as $tag) {
    $versions->addFromTags($tag->getName());
}
$versions->add('master', 'master branch');

return new Sami\Sami($dir, array(
    'build_dir' => 'build/%version%',
    'cache_dir' => 'cache/%version%',
    'title' => 'GitElephant API',
    'default_opened_level' => 1,
    'versions' => $versions
));