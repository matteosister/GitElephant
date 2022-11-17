<?php

/*
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace GitElephant\Sequence;

use PhpOption\Option;

/**
 * Interface for mutable sequences.
 *
 * Equality of elements in the sequence is established via a shallow comparison
 * (===).
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface SequenceInterface extends CollectionInterface
{
    /**
     * Returns the first element in the collection if available.
     *
     * @return Option
     */
    public function first(): Option;

    /**
     * Returns the last element in the collection if available.
     *
     * @return Option
     */
    public function last(): Option;

    /**
     * Returns all elements in this sequence.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Returns a new Sequence with all elements in reverse order.
     *
     * @return SequenceInterface
     */
    public function reverse(): SequenceInterface;

    /**
     * Adds the elements of another sequence to this sequence.
     *
     * @param SequenceInterface $seq
     *
     * @return SequenceInterface
     */
    public function addSequence(SequenceInterface $seq): SequenceInterface;

    /**
     * Returns the index of the passed element.
     *
     * @param mixed $elem
     *
     * @return integer the index (0-based), or -1 if not found
     */
    public function indexOf($elem): int;

    /**
     * Returns the last index of the passed element.
     *
     * @param mixed $elem
     *
     * @return integer the index (0-based), or -1 if not found
     */
    public function lastIndexOf($elem): int;

    /**
     * Returns whether the given index is defined in the sequence.
     *
     * @param integer $index (0-based)
     *
     * @return boolean
     */
    public function isDefinedAt(int $index): bool;

    /**
     * Returns the first index where the given callable returns true.
     *
     * @param callable $callable receives the element as first argument, and
     *   returns true, or false
     *
     * @return integer the index (0-based), or -1 if the callable returns false
     *   for all elements
     */
    public function indexWhere(callable $callable): int;

    /**
     * Returns the last index where the given callable returns true.
     *
     * @param callable $callable receives the element as first argument, and
     *   returns true, or false
     *
     * @return integer the index (0-based), or -1 if the callable returns false
     *   for all elements
     */
    public function lastIndexWhere(callable $callable): int;

    /**
     * Returns all indices of this collection.
     *
     * @return integer[]
     */
    public function indices(): array;

    /**
     * Returns the element at the given index.
     *
     * @param integer $index (0-based)
     *
     * @return mixed
     */
    public function get(int $index);

    /**
     * Adds an element to the sequence.
     *
     * @param mixed $elem
     *
     * @return void
     */
    public function add($elem): void;

    /**
     * Removes the element at the given index, and returns it.
     *
     * @param integer $index
     *
     * @return mixed
     */
    public function remove(int $index);

    /**
     * Adds all elements to the sequence.
     *
     * @param array $elements
     *
     * @return void
     */
    public function addAll(array $elements): void;

    /**
     * Updates the value at the given index.
     *
     * @param integer $index
     * @param mixed $value
     *
     * @return void
     */
    public function update(int $index, $value): void;

    /**
     * Returns a new sequence by omitting the given number of elements from the
     * beginning.
     *
     * If the passed number is greater than the available number of elements, all
     * will be removed.
     *
     * @param integer $number
     *
     * @return SequenceInterface
     */
    public function drop(int $number): SequenceInterface;

    /**
     * Returns a new sequence by omitting the given number of elements from the
     * end.
     *
     * If the passed number is greater than the available number of elements, all
     * will be removed.
     *
     * @param integer $number
     *
     * @return SequenceInterface
     */
    public function dropRight(int $number): SequenceInterface;

    /**
     * Returns a new sequence by omitting elements from the beginning for as long
     * as the callable returns true.
     *
     * @param callable $callable Receives the element to drop as first argument,
     *   and returns true (drop), or false (stop).
     *
     * @return SequenceInterface
     */
    public function dropWhile(callable $callable): SequenceInterface;

    /**
     * Creates a new collection by taking the given number of elements from the
     * beginning of the current collection.
     *
     * If the passed number is greater than the available number of elements,
     * then all elements will be returned as a new collection.
     *
     * @param integer $number
     *
     * @return CollectionInterface
     */
    public function take(int $number): CollectionInterface;

    /**
     * Creates a new collection by taking elements from the current collection
     * for as long as the callable returns true.
     *
     * @param callable $callable
     *
     * @return CollectionInterface
     */
    public function takeWhile(callable $callable): CollectionInterface;

    /**
     * Creates a new collection by applying the passed callable to all elements
     * of the current collection.
     *
     * @param callable $callable
     *
     * @return CollectionInterface
     */
    public function map(callable $callable): CollectionInterface;
}
