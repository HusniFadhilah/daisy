<?php

$providers = [
    App\Providers\AppServiceProvider::class,
];

if (class_exists(\BeyondCode\ErdGenerator\ErdGeneratorServiceProvider::class)) {
    $providers[] = \BeyondCode\ErdGenerator\ErdGeneratorServiceProvider::class;
}

return $providers;
