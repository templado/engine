<?php declare(strict_types = 1);
namespace Templado\Engine;

interface Serializer {
    public function serialize(Templado $document): string;
}
