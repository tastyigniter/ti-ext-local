<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/database',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkip([
        NewlineBeforeNewAssignSetRector::class,
        ReturnTypeFromStrictNewArrayRector::class,
    ])
    ->withPhpSets(php83: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
    );
