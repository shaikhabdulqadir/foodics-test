# Foodics Test

```bash
git clone https://github.com/shaikhabdulqadir/foodics-test.git
```

## Run Migration
```bash
php artisan migrate
```

## Run Seeder
```bash
php artisan db:seed
```

## Run Test Cases
```bash
php artisan test
```

## Place Order Code
```bash
\App\Http\Controllers\OrderController@create
```

## Place Order Code
```bash
\tests\Feature\CreateOrderTest.php
```

## Place Order API
`[POST] \api\order\create`

### Request
    {
        "products": [
            {
                "product_id": 1,
                "quantity": 2
            }
        ]
    }