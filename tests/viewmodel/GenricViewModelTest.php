<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

class GenricViewModelTest extends TestCase {

    private $model;

    protected function setUp() {
        $this->model = new GenericViewModel(['a' => 'value']);
    }

    public function testCallReturnsValueForDefinedProperty() {
        $this->assertEquals(
            'value', $this->model->a()
        );
    }

    public function testCallReturnsNullForUndefinedProperty() {
        $this->assertNull($this->model->undef());
    }

    public function testCallReturnsOriginalValueForUndefinedPropertyWhenGiven() {
        $this->assertEquals(
            'original', $this->model->undef('original')
        );
    }

}
