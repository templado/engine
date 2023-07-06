<?php declare(strict_types = 1);

$dom1 = new DOMDocument;
$dom1->loadXML('<?xml version="1.0" ?><with xmlns="some:ns" />');

$nodeA = $dom1->createElement('none');
$nodeB = $dom1->createElementNS(null, 'none');
$nodeC = $dom1->createElementNS('', 'none');

$dom1->documentElement->appendChild($nodeA);
$dom1->documentElement->appendChild($nodeB);
$dom1->documentElement->appendChild($nodeC);

echo    $dom1->saveXML();

$nodeA->namespaceURI = 'some:other';
var_dump($nodeA->namespaceURI, $nodeB->namespaceURI, $nodeC->namespaceURI);
