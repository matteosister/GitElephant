<?php
/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2013  Matteo Giachino
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see [http://www.gnu.org/licenses/].
 */

namespace GitElephant\Command;

use GitElephant\Command\Config\Config;
use GitElephant\TestCase;

/**
 * ConfigCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class ConfigCommandTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf('GitElephant\Command\ConfigCommand', ConfigCommand::getInstance());
    }

    public function testGet()
    {
        $this->assertEquals(
            "config '--local' '--get' 'user.name'",
            ConfigCommand::getInstance()->get('user.name')
        );
        $this->assertEquals(
            "config '--local' '--get' 'user.name'",
            ConfigCommand::getInstance()->get(Config::create('user', 'name'))
        );
    }

    public function testGetAll()
    {
        $this->assertEquals(
            "config '--local' '--get-all' 'user.name'",
            ConfigCommand::getInstance()->getAll('user.name')
        );
        $this->assertEquals(
            "config '--local' '--get-all' 'user.name'",
            ConfigCommand::getInstance()->getAll(Config::create('user', 'name'))
        );
    }

    public function testSet()
    {
        $this->assertEquals(
            "config '--local' 'user.name' 'test'",
            ConfigCommand::getInstance()->set('user.name', 'test')
        );
        $this->assertEquals(
            "config '--local' 'user.name' 'test'",
            ConfigCommand::getInstance()->set(Config::create('user', 'name'), 'test')
        );
    }

    public function testAdd()
    {
        $this->assertEquals(
            "config '--local' '--add' 'user.name' 'test'",
            ConfigCommand::getInstance()->add('user.name', 'test')
        );
        $this->assertEquals(
            "config '--local' '--add' 'user.name' 'test'",
            ConfigCommand::getInstance()->add(Config::create('user', 'name'), 'test')
        );
    }
}
