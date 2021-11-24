<?php declare(strict_types = 1);
namespace Templado\Engine;

class NegativeIndexVM {
    public function getYearList(): array {
        return [
            new YearListVM()
        ];
    }
}

class YearListVM {
    public function getLink() {
        return new class {
            public function asString() {
                return 'a year link text';
            }
        };
    }
}
