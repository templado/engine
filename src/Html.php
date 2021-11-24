<?php declare(strict_types = 1);

class Html {
    use Engine;

    public static function fromFile(FileName $fileName): static {
    }

    public static function fromString(string $string): static {
    }

    public static function fromDomDocument(DOMDocument $dom): static {
    }

    public function toFile(FileName $name): void {
    }

    public function toSnippet(?Selector $selector = null): Snippet {
    }

    public function toString(?Serializer $serializer = null): string {
    }

}
