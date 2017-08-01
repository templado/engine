<?php declare(strict_types = 1);
namespace Templado\Engine;

use Templado\Engine\Example\SampleTransformation;

require __DIR__ . '/../src/autoload.php';
require __DIR__ . '/transformation/SampleTransformation.php';

$page = Templado::loadHtmlFile(
    new FileName(__DIR__ . '/html/viewmodel.xhtml')
);

$page->applyTransformation(new SampleTransformation());

echo $page->asString();

