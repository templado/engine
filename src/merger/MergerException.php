<?php declare(strict_types = 1);
namespace Templado\Engine;

class MergerException extends Exception {

    const EmptyDocument = 1;
    const EmptyList = 2;
    const DuplicateId = 3;
}
