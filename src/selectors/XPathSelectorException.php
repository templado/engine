<?php declare(strict_types = 1);
namespace Templado\Engine;

class XPathSelectorException extends Exception {

    const InvalidExpression = 1207;
    const UnregisteredFunction = 1209;
    const UndefinedNamespace = 1219;

}
