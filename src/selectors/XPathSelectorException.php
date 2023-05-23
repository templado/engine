<?php declare(strict_types = 1);
namespace Templado\Engine;

use Exception;

class XPathSelectorException extends Exception {
    public const InvalidExpression    = 1207;
    public const UnregisteredFunction = 1209;
    public const UndefinedNamespace   = 1219;
}
