<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Signal::class)]
#[CoversClass(Ignore::class)]
#[CoversClass(Remove::class)]
#[CoversClass(NotDefined::class)]
#[Small]
class SignalTest extends TestCase {

    public function testCanCreateIgnoreSignal(): void {
        $inst = Signal::ignore();
        $this->assertTrue($inst->isIgnore());
        $this->assertFalse($inst->isRemove());
        $this->assertFalse($inst->isNotDefined());
    }

    public function testCanCreateRemoveSignal(): void {
        $inst = Signal::remove();
        $this->assertTrue($inst->isRemove());
        $this->assertFalse($inst->isIgnore());
        $this->assertFalse($inst->isNotDefined());
    }

    public function testCanCreateNotDefinedSignal(): void {
        $inst = Signal::notDefined();
        $this->assertTrue($inst->isNotDefined());
        $this->assertFalse($inst->isRemove());
        $this->assertFalse($inst->isIgnore());
    }
}
