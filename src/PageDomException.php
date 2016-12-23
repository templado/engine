<?php declare(strict_types = 1);
namespace TheSeer\Templado;

class PageDomException extends Exception {

    /**
     * @var \LibXMLError[]
     */
    private $errorList;

    public function __construct($message, $code = null, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errorList = libxml_get_errors();
        libxml_clear_errors();
    }

    /**
     * @return \LibXMLError[]
     */
    public function getErrorList(): array {
        return $this->errorList;
    }

}
