<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Assert;

trait DomDocumentsEqualTrait {

    private function assertResultMatches(DOMElement $expectedElement, DOMElement $actualElement): void {
        $ed = new DOMDocument();
        $ed->appendChild($ed->importNode($expectedElement, true));
        $xmlStr = $ed->C14N();

        $ed->preserveWhiteSpace = false;
        $ed->loadXML($xmlStr);
        $ed->formatOutput = true;

        $ad = new DOMDocument();
        $ad->appendChild($ad->importNode($actualElement, true));
        $xmlStr = $ad->C14N();

        $ad->preserveWhiteSpace = false;
        $ad->loadXML($xmlStr);
        $ad->formatOutput = true;

        Assert::assertEquals($ed->documentElement, $ad->documentElement);
    }
}
