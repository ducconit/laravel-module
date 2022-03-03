# LARAVEL MODULE

### SETUP
```
composer require dnt/laravel-module
```

- require php >= 8.1


### MODULE.JSON
- file in path: `modules/{namemodule}/module.json`
- template:
```json
{
  "name": "NameModule",
  "key": "key-module",
  "providers": [
    "namespace service provider"
  ],
  "aliases": {
    "alias": "namespace class"
  },
  "files": [
    "file path"
  ],
  "enable": true
}
```

### EXAMPLE
- Module cart manager
- Struct
```
|-app
|-bootstrap
|-config
...
|-modules
|----CartManager
|-------Facades
|-----------Cart.php
|-------Providers
|-----------CartManagerServiceProvider.php
|-------helpers.php
|-------module.json
...
|-composer.json
|.env
...
```
```json
{
  "name": "Cart Manager",
  "key": "cart-manager",
  "providers": [
    "CartManager\\Providers\\CartManagerServiceProvider"
  ],
  "aliases": {
    "Cart": "CartManager\\Facades\\Cart"
  },
  "files": [
    "./helpers.php"
  ],
  "enable": true
}
```

# HELPER
- get module
```php
app(\DNT\Module\Contracts\Management::class)->get('cart-manager');
```
- get all module
```php
app(\DNT\Module\Contracts\Management::class)->all();
```
- enable module
```php
app(\DNT\Module\Contracts\Management::class)->enable('cart-manager');
```
- disable module
```php
app(\DNT\Module\Contracts\Management::class)->enable('cart-manager');
```
- get view
```php
// Controller
...
return view('cart-manager::index');
...
```
- get translation
```php
__('cart-manager::name'); 
```
