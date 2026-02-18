<?php

return [
    App\Providers\AppServiceProvider::class,
    Shared\Infrastructure\Provider\SharedServiceProvider::class,
    BoundedContext\CourseCatalog\Infrastructure\Provider\CourseCatalogServiceProvider::class,
];
