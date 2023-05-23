<?php declare(strict_types = 1);

use PHPUnit\SebastianBergmann\Type\IterableType;

class Foo {

    public function bar(string|Iterable ...$a): void {
        var_dump($a);
    }
}

class Input implements IteratorAggregate  {

    public function getIterator() {
        return new ArrayIterator(['a', 'b']);
    }

    public function offsetExists(mixed $offset) {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet(mixed $offset) {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet(mixed $offset, mixed $value) {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset(mixed $offset) {
        // TODO: Implement offsetUnset() method.
    }

}


$f = new Foo();
$f->bar(new Input());

