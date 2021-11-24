<?php declare(strict_types = 1);
namespace Templado\Engine\ResourceModel;

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

class Header {
    public function getLabel(): string {
        return 'Changed text';
    }

    public function getLabel2(): string {
        return 'Changed text 2';
    }
}

class ResourceViewModel {
    public function getUser(): User {
        return new User();
    }

    public function getHeader(): Header {
        return new Header();
    }
}

class ResourceCallViewModel {
    public function __call($method, $args) {
        switch ($method) {
            case 'user': return new User();
            case 'header': return new Header();
        }

        throw new \RuntimeException('FAIL:' . $method);
    }
}
