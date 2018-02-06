<?php

class RemoveFromNullTestViewModel {

    public function getRemove() {
        return false;
    }

    public function getChoose() {
        return new class {
            public function typeOf(): string {
                return 'first';
            }
        };
    }

}
