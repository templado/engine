<?php declare(strict_types = 1);
namespace Templado\Engine;

class SnippetListCollection {

    /** @var SnippetList[] */
    private $snippetLists = [];

    public function addSnippet(Snippet $snippet): void {
        $id = $snippet->getTargetId();

        if (!$this->hasSnippetsForId($id)) {
            $this->snippetLists[$id] = new SnippetList();
        }
        $this->snippetLists[$id]->addSnippet($snippet);
    }

    public function hasSnippetsForId(string $id): bool {
        return isset($this->snippetLists[$id]);
    }

    /**
     * @throws SnippetCollectionException
     */
    public function getSnippetsForId(string $id): SnippetList {
        if (!$this->hasSnippetsForId($id)) {
            throw new SnippetCollectionException(
                \sprintf("No Snippets for Id '%s' in collection", $id)
            );
        }

        return $this->snippetLists[$id];
    }
}
