<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

foreach (['config', 'database', 'examples', 'resources'] as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $finder->in(__DIR__ . '/' . $dir);
    }
}

return (new Config())
    ->setRules([
        '@PER-CS2.0' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters', 'match'],
        ],
        'binary_operator_spaces' => [
            'default'   => 'single_space',
            'operators' => ['=>' => 'align_single_space_minimal'],
        ],
        'single_line_after_imports'   => true,
        'blank_line_after_namespace'  => true,
        'blank_line_after_opening_tag' => true,
        'class_attributes_separation' => [
            'elements' => ['method' => 'one'],
        ],
        'concat_space'                      => ['spacing' => 'one'],
        'new_with_parentheses'              => true,
        'not_operator_with_successor_space' => false,
        'single_quote'                      => true,
        'no_empty_statement'                => true,
        'no_extra_blank_lines'              => [
            'tokens' => ['extra'],
        ],
        'phpdoc_align'  => ['align' => 'left'],
        'phpdoc_scalar' => true,
        'phpdoc_trim'   => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
