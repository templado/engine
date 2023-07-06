<?php declare(strict_types = 1);
namespace Random\Engine;

use Templado\Engine\Document;
use Templado\Engine\Id;

require __DIR__ . '/src/autoload.php';

$target = Document::fromString('<html xmlns="http://www.w3.org/1999/xhtml"><body><span id="a" /><span id="b" /><span id="c" /></body></html>');

$snipA = Document::fromString('<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0"><div id="d" /><div id="e" /></templado:document>', new Id('a'));
$snipC = Document::fromString('<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0"><div id="f" /><div id="g" /></templado:document>', new Id('c'));
$snipD = Document::fromString('<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0"><div id="h" /><div id="i" /></templado:document>', new Id('d'));

$target->merge(
    $snipA,
    $snipC,
    $snipD
);


echo $target->asString();
