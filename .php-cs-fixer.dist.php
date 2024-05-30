<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude(
        [
            'vendor',
        ]
    )
    ->name('*.php')
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        '@PHP73Migration' => true,
        'global_namespace_import' => true,
        'self_static_accessor' => true,
        'void_return' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'single_quote' => ['strings_containing_single_quote_chars' => true],
        'yoda_style' => false,
        'blank_line_before_statement' => ['statements' => ['return']],
        'method_chaining_indentation' => false,
        'logical_operators' => true,
        'modernize_types_casting' => true,
        'php_unit_test_class_requires_covers' => false,
        'strict_comparison' => true,
        'psr_autoloading' => true,
        'combine_nested_dirname' => true,
        'is_null' => true,
        'no_alias_functions' => true,
        'no_unreachable_default_argument_value' => true,
        'dir_constant' => true,
        'no_alternative_syntax' => false,
        'method_argument_space' => false,
        'phpdoc_align' => ['align' => 'left'],
    ])
    ->setFinder($finder);
