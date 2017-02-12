<?php declare(strict_types=1);
namespace TheSeer\Templado;

use DOMNode;

interface Asset {

    /**
     * @return string
     */
    public function getTargetId(): string;

    /**
     * @return DOMNode
     */
    public function getContent(): DOMNode;

    /**
     * @return bool
     */
    public function replaceCurrent(): bool;

}
