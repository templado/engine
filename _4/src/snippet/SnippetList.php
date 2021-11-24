<?php declare(strict_types = 1);
namespace Templado\Engine;

class SnippetList implements \Iterator, \Countable {

    /** @var Snippet[] */
    private $snippets = [];

    public function addSnippet(Snippet $snippet): void {
        $this->snippets[] = $snippet;
    }

    public function current(): Snippet {
        return \current($this->snippets);
    }

    public function next(): void {
        \next($this->snippets);
    }

    public function key(): int {
        return \key($this->snippets);
    }

    public function valid(): bool {
        return \key($this->snippets) !== null;
    }

    public function rewind(): void {
        \reset($this->snippets);
    }

    public function count(): int {
        return \count($this->snippets);
    }
}
