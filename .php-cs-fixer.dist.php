<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/tests')
	->exclude('temp')
	->name(['*.php', '*.phpt'])
;

return (new PhpCsFixer\Config())
	->setUsingCache(FALSE)
	->setIndent("\t")
	->setRules([
		'@PSR2' => TRUE,
		'array_syntax' => ['syntax' => 'short'],
		'trailing_comma_in_multiline' => [
			'elements' => ['arrays'],
		],
		'constant_case' => [
			'case' => 'upper',
		],
		'declare_strict_types' => TRUE,
		'phpdoc_align' => TRUE,
		'blank_line_after_opening_tag' => TRUE,
		'blank_line_before_statement' => [
			'statements' => ['break', 'continue', 'declare', 'return'],
		],
		'blank_line_after_namespace' => TRUE,
		'blank_lines_before_namespace' => [
			'min_line_breaks' => 2,
			'max_line_breaks' => 2,
		],
		'return_type_declaration' => [
			'space_before' => 'none',
		],
		'ordered_imports' => [
			'sort_algorithm' => 'length',
		],
		'no_unused_imports' => TRUE,
		'single_line_after_imports' => TRUE,
		'no_leading_import_slash' => TRUE,
	])
	->setRiskyAllowed(TRUE)
	->setFinder($finder)
;
