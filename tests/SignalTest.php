<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Signal::class)]
#[CoversClass(Ignore::class)]
#[CoversClass(Remove::class)]
class SignalTest extends TestCase {

    public function testCanCreateIgnoreSignal(): void {
        $inst = Signal::ignore();
        $this->assertTrue($inst->isIgnore());
        $this->assertFalse($inst->isRemove());
    }

    public function testCanCreateRemoveSignal(): void {
        $inst = Signal::remove();
        $this->assertTrue($inst->isRemove());
        $this->assertFalse($inst->isIgnore());
    }
}
