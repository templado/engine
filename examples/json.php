<?php declare(strict_types = 1);
namespace Templado\Engine;

require __DIR__ . '/../src/autoload.php';

try {
    $page = Templado::loadFile(
        new FileName(__DIR__ . '/html/viewmodel.xhtml')
    );

    $input = file_get_contents(__DIR__ . '/viewmodel/viewmodel.json');
    $mapper = new JsonMapper();
    $obj = $mapper->fromString($input);

    $page->applyViewModel($obj);

    echo $page->asString() . "\n";

} catch (TempladoException $e) {
    foreach($e->getErrorList() as $error) {
        echo (string)$error;
    }
}
