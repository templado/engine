<?php declare(strict_types = 1);
namespace Templado\Engine;

class Issue5_Button {
    private $action;
    public function __construct(string $action) {
        $this->action = $action;
    }
    public function getFormaction() {
        return $this->action;
    }

    public function asString(): string {
        return 'Changed action to: ' . $this->action;
    }
}

class Issue5_ViewData {
    public function getButton1() {
        return new Issue5_Button('/target1');
    }
    public function getButton2() {
        return new Issue5_Button('/target2');
    }
    public function getButton3() {
        return new Issue5_Button('/target3');
    }
    public function getButton4() {
        return new Issue5_Button('/target4');
    }
}
