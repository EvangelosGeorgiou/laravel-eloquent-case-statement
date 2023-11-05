
# Laravel Eloquent Case Statement Builder

### Installation

```
composer require evangeo/laravel-eloquent-case-statement
```


## Running Tests

To run tests, run the following command

```bash
  composer test
```


## Eloquent Case Builder

The Laravel Eloquent Case is a helpful tool for building query case statements in your Laravel application.
This can help you write cleaner, more readable code.

### Usage/Examples

#### The Case Builder will help you transform MySql Query Statement from this :

```
SELECT
CASE
    WHEN Quantity > 30 THEN "The quantity is greater than 30"
    WHEN Quantity = 30 THEN "The quantity is 30"
    ELSE "The quantity is under 30"
END AS result
FROM OrderDetails;
```

#### To this :

```
OrderDetails::query()->case([
    when('Quantity', '>', 30)->then("The quantity is greater than 30"),
    when('Quantity', '=', 30)->then("The quantity is 30"),
], "The quantity is under 30", "result")
```

- The `case()` method take 3 parameters.
    - The `$whens` parameter is an array of `when()` cases. Each `when()` case specifies a condition that will be evaluated. On the above example are the two when statements.
    - The `$else` parameter is the default value that will be returned if none of the `when()` conditions are met. This can be useful for providing a default result in case none of the conditions are satisfied. On the above example the else statement is the **"The quantity is under 30"**
    - The `$alias` parameter is an optional parameter that allows you to give your case statement an alias. This can be useful for referencing the case statement in subsequent queries or in your code. On the above example is the value **"result"**

#### Some more examples

```
SELECT CASE
    WHEN status_id < 30 and ( type_id = 2 or type_id = 6) 
            THEN CASE
                WHEN role_id = 1 THEN employee
                WHEN role_id = 2 THEN manager
                WHEN role_id = 3 THEN admin
                ELSE N/A
            END
    ELSE NULL                                                        
END AS result
from orders
```

```
DB::table("orders")->case([
        when("status_id", '<', 30)
            ->and(function (LogicalBuilder $q) {
                $q->or('type_id', '=', 2)
                    ->or('type_id', '=', 6);
            })->then([
                when("role_id", 1)->then('employee'),
                when("role_id", 2)->then('manager'),
                when("role_id", 3)->then('admin'),
            ], 'N/A'
    )], 'NULL', 'RESULT')
])
```