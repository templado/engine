<?php declare(strict_types = 1);

namespace Templado\Engine\Example;

class Headline {
    public function asString() {
        return 'Hallo welt!';
    }

    public function class($original) {
        return $original . ' added';
    }

    public function title() {
        return 'new Title';
    }
}

class Email {
    private $addr;

    public function __construct(string $addr) {
        $this->addr = $addr;
    }

    public function asString() {
        return $this->addr;
    }

    public function href() {
        return 'mailto:' . $this->asString();
    }

    public function class() {
        return false;
    }
}

class EMailLink {

    /** @var  Email */
    private $email;

    /**
     * @param Email $email
     */
    public function __construct(Email $email) {
        $this->email = $email;
    }

    public function email() {
        return $this->email;
    }
}

class User {

    public function name() {
        return 'Willi Wichtig';
    }

    public function emailLinks() {
        return [
            new EMailLink(
                new Email('willi@wichtig.de')
            ),
            new EMailLink(
                new Email('second@secondis.de')
            )
        ];
    }

}

class ViewModel {

    public function headline() {
        return new Headline();
    }

    public function test() {
        return ['a', 'b'];
    }

    public function user() {
        return new User();
    }
}
