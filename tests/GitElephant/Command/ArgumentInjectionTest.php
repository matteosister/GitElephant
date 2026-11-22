<?php

declare(strict_types=1);

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GitElephant\Command;

use GitElephant\Objects\NodeObject;
use GitElephant\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ArgumentInjectionTest extends TestCase
{
    /**
     * @return iterable<string, array{0: callable(): string}>
     */
    public static function optionLikeCommandProvider(): iterable
    {
        yield 'LogCommand::showLog' => [fn (): string => LogCommand::getInstance()->showLog('--output=/tmp/pwn')];
        yield 'LogCommand::showLog array' => [fn (): string => LogCommand::getInstance()->showLog(['HEAD', '--output=/tmp/pwn'])];
        yield 'LogRangeCommand::showLog start' => [fn (): string => LogRangeCommand::getInstance()->showLog('--output=/tmp/pwn', 'HEAD')];
        yield 'LogRangeCommand::showLog end' => [fn (): string => LogRangeCommand::getInstance()->showLog('HEAD', '--output=/tmp/pwn')];
        yield 'ShowCommand::showCommit' => [fn (): string => ShowCommand::getInstance()->showCommit('--output=/tmp/pwn')];
        yield 'ShowCommand::showCommit stringable' => [fn (): string => ShowCommand::getInstance()->showCommit(new class implements \Stringable {
            public function __toString(): string
            {
                return '--output=/tmp/pwn';
            }
        })];
        yield 'DiffCommand::diff of' => [fn (): string => DiffCommand::getInstance()->diff('--output=/tmp/pwn')];
        yield 'DiffCommand::diff with' => [fn (): string => DiffCommand::getInstance()->diff('HEAD', '--output=/tmp/pwn')];
        yield 'CloneCommand::cloneUrl url' => [fn (): string => CloneCommand::getInstance()->cloneUrl('--upload-pack=id')];
        yield 'CloneCommand::cloneUrl to' => [fn (): string => CloneCommand::getInstance()->cloneUrl('https://example.com/repo.git', '--upload-pack=id')];
        yield 'FetchCommand::fetch remote' => [fn (): string => FetchCommand::getInstance()->fetch('--upload-pack=id')];
        yield 'FetchCommand::fetch branch' => [fn (): string => FetchCommand::getInstance()->fetch('origin', '--upload-pack=id')];
        yield 'PullCommand::pull remote' => [fn (): string => PullCommand::getInstance()->pull('--upload-pack=id')];
        yield 'PullCommand::pull branch' => [fn (): string => PullCommand::getInstance()->pull('origin', '--upload-pack=id')];
        yield 'PushCommand::push remote' => [fn (): string => PushCommand::getInstance()->push('--receive-pack=id', 'main')];
        yield 'PushCommand::push branch' => [fn (): string => PushCommand::getInstance()->push('origin', '--receive-pack=id')];
        yield 'RemoteCommand::add name' => [fn (): string => RemoteCommand::getInstance()->add('--mirror=fetch', 'https://example.com/repo.git')];
        yield 'RemoteCommand::add url' => [fn (): string => RemoteCommand::getInstance()->add('origin', '--mirror=fetch')];
        yield 'RemoteCommand::show name' => [fn (): string => RemoteCommand::getInstance()->show('--help')];
        yield 'MainCommand::add' => [fn (): string => MainCommand::getInstance()->add('--force')];
        yield 'MainCommand::checkout' => [fn (): string => MainCommand::getInstance()->checkout('--force')];
        yield 'MainCommand::move from' => [fn (): string => MainCommand::getInstance()->move('--force', 'dest')];
        yield 'MainCommand::move to' => [fn (): string => MainCommand::getInstance()->move('src', '--force')];
        yield 'BranchCommand::contains' => [fn (): string => BranchCommand::getInstance()->contains('--delete')];
        yield 'BranchCommand::create name' => [fn (): string => BranchCommand::getInstance()->create('-D')];
        yield 'BranchCommand::create startPoint' => [fn (): string => BranchCommand::getInstance()->create('feature', '--track')];
        yield 'BranchCommand::singleInfo' => [fn (): string => BranchCommand::getInstance()->singleInfo('--delete')];
        yield 'BranchCommand::delete' => [fn (): string => BranchCommand::getInstance()->delete('--all')];
        yield 'TagCommand::create name' => [fn (): string => TagCommand::getInstance()->create('-d')];
        yield 'TagCommand::create startPoint' => [fn (): string => TagCommand::getInstance()->create('v1.0', '-d')];
        yield 'TagCommand::delete' => [fn (): string => TagCommand::getInstance()->delete('--force')];
        yield 'LsTreeCommand::fullTree' => [fn (): string => LsTreeCommand::getInstance()->fullTree('--name-only')];
        yield 'LsTreeCommand::tree' => [fn (): string => LsTreeCommand::getInstance()->tree('--name-only')];
        yield 'LsTreeCommand::listAll' => [fn (): string => LsTreeCommand::getInstance()->listAll('--name-only')];
        yield 'CatFileCommand::contentBySha' => [fn (): string => CatFileCommand::getInstance()->contentBySha('--batch')];
        yield 'MvCommand::rename source' => [fn (): string => MvCommand::getInstance()->rename('--force', 'dest')];
        yield 'MvCommand::rename target' => [fn (): string => MvCommand::getInstance()->rename('src', '--force')];
        yield 'ResetCommand::reset' => [fn (): string => ResetCommand::getInstance()->reset('--hard')];
        yield 'RevParseCommand::revParse' => [fn (): string => RevParseCommand::getInstance()->revParse('--git-dir')];
        yield 'StashCommand::save message' => [fn (): string => StashCommand::getInstance()->save('--include-untracked')];
        yield 'StashCommand::branch' => [fn (): string => StashCommand::getInstance()->branch('--help', '0')];
        yield 'SubmoduleCommand::add url' => [fn (): string => SubmoduleCommand::getInstance()->add('--force')];
        yield 'SubmoduleCommand::add path' => [fn (): string => SubmoduleCommand::getInstance()->add('https://example.com/repo.git', '--force')];
    }

    #[DataProvider('optionLikeCommandProvider')]
    public function testOptionLikeArgumentIsRefused(callable $commandBuilder): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $commandBuilder();
    }

    public function testCatFileContentRefusesOptionLikeTreeish(): void
    {
        $node = $this->createStub(NodeObject::class);
        $this->expectException(\InvalidArgumentException::class);
        CatFileCommand::getInstance()->content($node, '--batch');
    }
}
