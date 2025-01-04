<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'blank_line_before_statement' => false,
        'concat_space' => ['spacing' => 'one'],
        'elseif' => false,
        'increment_style' => false,
        'single_line_empty_body' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder)
;
