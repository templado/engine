<?php declare(strict_types=1);
/*
 * This file is part of Templado\Engine.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Templado\Engine;

final class ViewModelRendererException extends Exception {
    public const NotInDocument                     = 1;
    public const InvalidPrefixDefinition           = 2;
    public const PrefixResolvingFailed             = 3;
    public const WrongTypeForPrefix                = 4;
    public const ResourceResolvingWithPrefixFailed = 5;
    public const ResourceResolvingFailed           = 6;
    public const WrongTypeForResource              = 7;
    public const NoModelForPrefix                  = 8;
    public const WrongTypeForVocab                 = 9;
    public const ResolvingPropertyFailed           = 10;
    public const WrongTypeForTypeOf                = 11;
    public const UnsupportedTypeForProperty        = 12;
    public const TypeOfMethodRequired              = 13;
    public const NoMatch                           = 14;
    public const IterableForRootElement            = 15;
    public const StringRequired                    = 16;
    public const WrongTypeForAttribute             = 17;
    public const WrongTypeForParameter             = 18;
}
