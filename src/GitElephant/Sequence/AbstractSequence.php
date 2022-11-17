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

use OutOfBoundsException;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

/**
 * A sequence with numerically indexed elements.
 *
 * This is rawly equivalent to an array with only numeric keys.
 * There are no restrictions on how many same values may occur in the sequence.
 *
 * This sequence is mutable.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AbstractSequence extends AbstractCollection implements \IteratorAggregate, SequenceInterface
{
    protected $elements;

    /**
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = array_values($elements);
    }

    public function addSequence(SequenceInterface $seq): SequenceInterface
    {
        $this->addAll($seq->all());
        return $this;
    }

    public function indexOf($elem): int
    {
        foreach ($this->elements as $i => $element) {
            if ($elem === $element) {
                return $i;
            }
        }

        return -1;
    }

    public function lastIndexOf($elem): int
    {
        for ($i = count($this->elements) - 1; $i >= 0; $i--) {
            if ($this->elements[$i] === $elem) {
                return $i;
            }
        }

        return -1;
    }

    public function reverse(): SequenceInterface
    {
        return $this->createNew(array_reverse($this->elements));
    }

    public function isDefinedAt(int $index): bool
    {
        return isset($this->elements[$index]);
    }

    /**
     * Returns a filtered sequence.
     *
     * @param callable $callable receives the element and must return true (=
     *   keep) or false (= remove).
     *
     * @return CollectionInterface
     */
    public function filter(callable $callable): CollectionInterface
    {
        return $this->filterInternal($callable, true);
    }

    public function map(callable $callable): CollectionInterface
    {
        $newElements = [];
        foreach ($this->elements as $i => $element) {
            $newElements[$i] = $callable($element);
        }

        return $this->createNew($newElements);
    }

    /**
     * Returns a filtered sequence.
     *
     * @param callable $callable receives the element and must return true (=
     *   remove) or false (= keep).
     *
     * @return CollectionInterface
     */
    public function filterNot(callable $callable): CollectionInterface
    {
        return $this->filterInternal($callable, false);
    }

    private function filterInternal($callable, $booleanKeep): CollectionInterface
    {
        $newElements = [];
        foreach ($this->elements as $element) {
            if ($booleanKeep !== $callable($element)) {
                continue;
            }

            $newElements[] = $element;
        }

        return $this->createNew($newElements);
    }

    public function foldLeft($initialValue, $callable)
    {
        $value = $initialValue;
        foreach ($this->elements as $elem) {
            $value = $callable($value, $elem);
        }

        return $value;
    }

    public function foldRight($initialValue, $callable)
    {
        $value = $initialValue;
        foreach (array_reverse($this->elements) as $elem) {
            $value = $callable($elem, $value);
        }

        return $value;
    }

    /**
     * Finds the first index where the given callable returns true.
     *
     * @param callable $callable
     *
     * @return integer the index, or -1 if the predicate is not true for any
     *   element.
     */
    public function indexWhere(callable $callable): int
    {
        foreach ($this->elements as $i => $element) {
            if ($callable($element) === true) {
                return $i;
            }
        }

        return -1;
    }

    public function lastIndexWhere(callable $callable): int
    {
        for ($i = count($this->elements) - 1; $i >= 0; $i--) {
            if ($callable($this->elements[$i]) === true) {
                return $i;
            }
        }

        return -1;
    }

    public function last(): Option
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(end($this->elements));
    }

    public function first(): Option
    {
        if (empty($this->elements)) {
            return None::create();
        }

        return new Some(reset($this->elements));
    }

    public function indices(): array
    {
        return array_keys($this->elements);
    }

    public function get(int $index)
    {
        if (!isset($this->elements[$index])) {
            throw new OutOfBoundsException(sprintf('The index "%s" does not exist in this sequence.', $index));
        }

        return $this->elements[$index];
    }

    public function remove(int $index)
    {
        if (!isset($this->elements[$index])) {
            throw new OutOfBoundsException(sprintf('The index "%d" is not in the interval [0, %d).', $index, count($this->elements)));
        }

        $element = $this->elements[$index];
        unset($this->elements[$index]);
        $this->elements = array_values($this->elements);

        return $element;
    }

    public function update(int $index, $value): void
    {
        if (!isset($this->elements[$index])) {
            throw new \InvalidArgumentException(sprintf('There is no element at index "%d".', $index));
        }

        $this->elements[$index] = $value;
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    public function all(): array
    {
        return $this->elements;
    }

    public function add($elem): void
    {
        $this->elements[] = $elem;
    }

    public function addAll(array $elements): void
    {
        foreach ($elements as $newElement) {
            $this->elements[] = $newElement;
        }
    }

    public function take(int $number): CollectionInterface
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('$number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, $number));
    }

    /**
     * Extracts element from the head while the passed callable returns true.
     *
     * @param callable $callable receives elements of this sequence as first
     *   argument, and returns true/false.
     *
     * @return CollectionInterface
     */
    public function takeWhile(callable $callable): CollectionInterface
    {
        $newElements = [];

        foreach ($this->elements as $i => $iValue) {
            if ($callable($this->elements[$i]) !== true) {
                break;
            }

            $newElements[] = $iValue;
        }

        return $this->createNew($newElements);
    }

    public function drop(int $number): SequenceInterface
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, $number));
    }

    public function dropRight(int $number): SequenceInterface
    {
        if ($number <= 0) {
            throw new \InvalidArgumentException(sprintf('The number must be greater than 0, but got %d.', $number));
        }

        return $this->createNew(array_slice($this->elements, 0, -1 * $number));
    }

    public function dropWhile(callable $callable): SequenceInterface
    {
        $i = 0;
        foreach ($this->elements as $i => $iValue) {
            if (true !== $callable($this->elements[$i])) {
                break;
            }
        }

        return $this->createNew(array_slice($this->elements, $i));
    }

    public function exists($callable): bool
    {
        foreach ($this as $elem) {
            if ($callable($elem) === true) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements ?: []);
    }

    protected function createNew(array $elements): SequenceInterface
    {
        return new static($elements);
    }
}
