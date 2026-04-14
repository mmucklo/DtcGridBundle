<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('Resources');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'nullable_type_declaration_for_default_null_value' => false,
    ])
    ->setFinder($finder);
