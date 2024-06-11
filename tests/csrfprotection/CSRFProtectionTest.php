<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Templado\Engine\CSRFProtection::class)]
class CSRFProtectionTest extends TestCase {

    /** @var CSRFProtection */
    private $csrfprotection;

    protected function setUp(): void {
        $this->csrfprotection = new CSRFProtection('fieldname', 'token-string');
    }

    public function testFieldNameCanBeRetrieved(): void {
        $this->assertEquals(
            'fieldname',
            $this->csrfprotection->fieldName()
        );
    }

    public function testTokenValueCanBeRetrieved(): void {
        $this->assertEquals(
            'token-string',
            $this->csrfprotection->tokenValue()
        );
    }
}
