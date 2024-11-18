## Larapps

This is a boilerplate application for installing an app to bigcommerce.


## Installation

You can install the package via composer:

```bash
composer require larapps/bigcommerce-app
```

### Needed ENV variables
```
APP_CLIENT_ID=XXXXXXXXXXXXXXXXX
APP_SECRET_KEY=XXXXXXXXXXXXXXXXX
APP_URL="https://api.bigcommerce.com/stores/"
```

### Migration
```php artisan migrate```

To create a table for maintaining bigcommerce access tokens.

### Implementation
```
routes/web.php

use Illuminate\Http\Request;
use Larapps\BigcommerceApp\BigcommerceApp;

Route::get('/auth/install', function(Request $request){
    $bigcommerceApp = new BigcommerceApp();
    return $bigcommerceApp->install( $request );
})->name('app.install');

Route::get('/auth/load', function(Request $request){
    $bigcommerceApp = new BigcommerceApp();
    return $bigcommerceApp->load( $request );
})->name('app.load');

Route::get('/auth/uninstall', function(Request $request){
    $bigcommerceApp = new BigcommerceApp();
    return $bigcommerceApp->uninstall( $request );
})->name('app.uninstall');

```


### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email balashanmugam.srm@gmail.com instead of using the issue tracker.

## Credits

-   [Balashanmugam](https://github.com/larapps)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
