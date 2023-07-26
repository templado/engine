<?php declare(strict_types = 1);
namespace Templado\Engine\Example;

use Templado\Engine\Remove;
use Templado\Engine\Signal;

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

    public function dataRemove(): Remove {
        return Signal::remove();
    }
}

class EMailLink {

    /** @var Email */
    private $email;

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
    public function getHeadline() {
        return new Headline();
    }

    public function test() {
        return ['a', 'b'];
    }

    public function user() {
        return new User();
    }

    public function boolSample() {
        return false;
    }
}
