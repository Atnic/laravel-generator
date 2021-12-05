# Laravel Generator

## Installation
```bash
composer require atnic/laravel-generator
```

## Make Module (CRUD)
This package is overriding some laravel artisan command.

This is example to make Foo module in this project
```bash
php artisan make:controller --model=Foo FooController
```
If create custom directory for model like this:
```
php artisan make:controller --model=App\\Models\\Foo FooController
```
Then do this steps:
- [x] Check new migration in `database/migrations/`, add column needed.
- [x] Check new factory in `database/factories/`, add atrribute needed.
- [x] Check new model in `app/`, add changes needed.
- [x] Check new filter in `app/Filters/`, do all `TODO:` and remove the comment if done.
- [x] Check lang en `resources/lang/en` and copy from en to lang id `resources/lang/id`, add language as needed.
- [x] Check new controller in `app/Http/Controllers/`, complete returned array in method `relations()` `visibles()` `fields()` `rules()`, do all `TODO:`, and remove comment if done.
- [x] Check new policy in `app/Policies/`, do all `TODO:` and remove the comment if done.
- [x] No need to append new Policy to `$policies` attribute in `app/Providers/AuthServiceProvider.php`. This package handle policy auto discovery, even for Laravel < 5.8.
- [x] Check new views (index, create, show, edit) in `resources/views/`, add/extend section for title or anything.
- [x] Check new tests in `tests/Feature/`, do all `TODO:` and remove the comment if done.

## Other Useful command

```bash
#Creating Nested Controller
php artisan make:controller --parent=Foo --model=Bar Foo/BarController

#Creating Nested Controller with custom directory for model
php artisan make:controller --parent=App\\Models\\Foo --model=App\\Models\\Bar Foo/BarController

#Create Single Action Controller
php artisan make:controller DashboardController

#Creating Api Controller
php artisan make:controller-api --model=Foo FooController

#Creating Api Controller with custom directory for model
php artisan make:controller-api --model=App\\Models\\Foo FooController

#Creating Nested Controller API
php artisan make:controller-api --parent=Foo --model=Bar Foo/BarController

#Creating Nested Controller API with custom directory for model
php artisan make:controller-api --parent=App\\Models\\Foo --model=App\\Models\\Bar Foo/BarController
```

All new/overrided command can be viewed in `vendor/atnic/laravel-generator/app/Console/Commands`.
