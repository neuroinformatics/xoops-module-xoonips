<?php

// .php_cs

namespace PhpCsFixer;

return Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@Symfony' => true,
        'array_syntax' => [
            'syntax' => 'long',
        ],
    ))
    ->setFinder(
        Finder::create()
            ->exclude('contrib')
            ->in(__DIR__.'/xoonips')
    )
;
