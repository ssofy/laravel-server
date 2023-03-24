<?php

use PhpCsFixer\{Config, Finder};

$rules = [
    '@PSR2' => true,
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(false)
    ->setUsingCache(false);
