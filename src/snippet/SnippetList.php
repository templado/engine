<?php declare(strict_types = 1);
namespace Templado\Engine;

class SnippetList implements \Iterator, \Countable {

    /** @var Snippet[] */
    private $snippets = [];

    public function addSnippet(Snippet $snippet) {
        $this->snippets[] = $snippet;
    }

    public function current(): Snippet {
        return current($this->snippets);
    }

    /**
     * @return mixed|Snippet
     */
    public function next() {
        return next($this->snippets);
    }

    public function key(): int {
        return key($this->snippets);
    }

    public function valid(): bool {
        return key($this->snippets) !== null;
    }

    public function rewind() {
        return reset($this->snippets);
    }

    public function count(): int {
        return \count($this->snippets);
    }

}
