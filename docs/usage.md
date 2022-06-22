# Usage

> Leveraging the `GalDigitalGmbh\Validation\Symfony\Validator` service

## When to use

If you have a simple set of validation rules that need to be checked before a given input can be used, you can use this type of validation.  
For example:

- A single email field
- A pagination with `page: int<0, max>` and `pageSize: 10|25|50`
- A non-empty list of IDs `int[]`
- ...

## When NOT to use

If you have very complex validation rules that maybe even change depending on different contexts, you should not use this type of simple validation. You should use [symfony forms](https://symfony.com/doc/current/forms.html#creating-form-classes) instead.  
For example:

- User registration inputs
- A filterable and sortable table with a lot of columns
- ...

## How to use

In controllers, you can use `$this->validate(/* ... */)` directly.  
In services, you can add the `GalDigitalGmbh\Validation\Symfony\Traits\HasValidator` trait and then use `$this->validate(/* ... */)`.

### Basic validation

> This validates all parameters of the request bags merged by `query`, `request`, `files` and `attributes` in this order

```php
// Request example (GET and POST possible):
// GET /my/api/route?param1=abc&param2=def
// POST /my/api/route
//      'param1' => 'abc',
//      'param2' => 'def',

$data = $this->validate([
    'param1' => [
        new NotBlank(),
    ],
]);

dd($data); // => Is [ 'param1' => 'abc' ]
```

### Only validate a specific request bag

> This validates only the given request bag, like `$request->query`

```diff
- // Request example (GET and POST possible):
+ // Request example (only POST possible):
- // GET /my/api/route?param1=abc&param2=def
  // POST /my/api/route
  //      'param1' => 'abc',
  //      'param2' => 'def',

  $data = $this->validate([
      'param1' => [
          new NotBlank(),
      ],
- ]);
+ ], $request->request);

  dd($data); // => Is [ 'param1' => 'abc' ]
```

### Constraints

> Use [symfony constraints](https://symfony.com/doc/current/reference/constraints.html), they work exactly like form constraints

```diff
  // Request example (GET and POST possible):
- // GET /my/api/route?param1=abc&param2=def
+ // GET /my/api/route?param1=abc@d.ef&param2[]=def
  // POST /my/api/route
- //      'param1' => 'abc',
+ //      'param1' => 'abc@d.ef',
- //      'param2' => 'def',
+ //      'param2' => [
+ //          'def',
+ //      ],

  $data = $this->validate([
      'param1' => [
          new NotBlank(),
+         new Email(),
+     ],
+     'param2' => new Collection([
+         new NotBlank(),
+     ],
  ]);

- dd($data); // => Is [ 'param1' => 'abc' ]
+ dd($data); // => Is [ 'param1' => 'abc@d.ef', 'param2' => [ 0 => 'def' ] ]
```

## Handling errors

If some constraints are invalid, the `$this->validate()` call throws a `GalDigitalGmbh\Validation\Symfony\Exception\ValidationException`.  
If you do not handle the exception yourself, the response output will be a validation error JSON.  
For example:

```php
// Request example:
// GET /my/api/route?email=invalidValue

$data = $this->validate([
    'email' => [
        new NotBlank(),
        new Email(),
    ],
], $request->query);
```

Response JSON:

```json
{
   "message": "Validation error",
   "errors": {
      "[email]": [
         "This value is not a valid email address."
      ]
   }
}
```

### Custom error handling

To not throw a validation exception with a JSON response - for example in controller page actions - you can simply catch the `GalDigitalGmbh\Validation\Symfony\Exception\ValidationException` and handle the violations yourself. The violations are the same result as form constraint violations.  
For example:

```diff
+ try {
      $data = $this->validate([
          'email' => [
              new NotBlank(),
              new Email(),
          ],
      ], $request->query);
+ } catch (ValidationException $exception) {
+     $violations = $exception->getViolations();
+     // Handle $violations yourself
+ }
```

## Advanced custom usage

If you want to use all features of the symfony validator, use `$this->validator->validate()` instead of `$this->validate()`. For the extended usage, see the [symfony validator documentation](https://symfony.com/doc/current/components/validator.html)

The only difference between this validator and the symfony validator is the last method argument `$throwException`.

If true (default), the validator will throw a validation exception that is handled automatically and returns a JSON response.

If false, the validator behaves just like the symfony validator.
