<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

class CSRFProtectionTest extends TestCase {

    /**
     * @var CSRFProtection
     */
    private $csrfprotection;

    protected function setUp() {
        $this->csrfprotection = new CSRFProtection('fieldname', 'token-string');
    }

    public function testFieldNameCanBeRetrieved() {
        $this->assertEquals(
            'fieldname',
            $this->csrfprotection->getFieldName()
        );
    }

    public function testTokenValueCanBeRetrieved() {
        $this->assertEquals(
            'token-string',
            $this->csrfprotection->getTokenValue()
        );
    }

}
