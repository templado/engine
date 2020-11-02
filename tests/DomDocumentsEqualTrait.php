<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Assert;
use RuntimeException;

trait DomDocumentsEqualTrait {

    private function assertResultMatches(DOMElement $expectedElement, DOMElement $actualElement): void {
        \libxml_clear_errors();
        $org = \libxml_use_internal_errors(true);

        $ed = new DOMDocument();
        $ed->appendChild($ed->importNode($expectedElement, true));
        $xmlStr = $ed->C14N();
        if (!$xmlStr) {
            $error = \libxml_get_last_error();
            \libxml_use_internal_errors($org);
            throw new RuntimeException('expectedElement: ' . $error->message, $error->code);
        }

        $ed->preserveWhiteSpace = false;
        $ed->loadXML($xmlStr);
        $ed->formatOutput = true;

        $ad = new DOMDocument();
        $ad->appendChild($ad->importNode($actualElement, true));
        $xmlStr = $ad->C14N();
        if (!$xmlStr) {
            $error = \libxml_get_last_error();
            \libxml_use_internal_errors($org);
            throw new RuntimeException('actualElement: ' . $error->message, $error->code);
        }

        $ad->preserveWhiteSpace = false;
        $ad->loadXML($xmlStr);
        $ad->formatOutput = true;

        Assert::assertEquals($ed->documentElement, $ad->documentElement);
        \libxml_use_internal_errors($org);
    }
}
