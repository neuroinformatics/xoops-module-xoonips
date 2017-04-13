<?php

// .php_cs

namespace PhpCsFixer;

return Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@Symfony' => true,
    ))
    ->setFinder(
        Finder::create()
            ->exclude('contrib')
            ->in(__DIR__.'/xoonips')
    )
;
