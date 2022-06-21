# simple validations for pimcore

Provide helper to validate input

## Installation

Require the bundle

```bash
composer require gal-digital-gmbh/validation
```

Add to `config/bundles.php`
```php
<?php

return [
    GalDigitalGmbh\Validation\ValidationBundle::class => ['all' => true],
];
```


## Sample

```
<?php

// src/Controller/DefaultController.php

namespace App\Controller;

use GalDigitalGmbh\Validation\Symfony\Traits\HasValidator;
use Pimcore\Model\Document\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Type;

class DefaultController extends FrontendController
{
    use HasValidator;

    public function defaultAction(Request $request): Response
    {
        $data = $this->validate([
            'test' => [
                new NotBlank(),
            ],
            'offset' => new Collection([
                0 => new Collection([
                    'abc' => [
                        new NotBlank(),
                        new Type('digit'),
                        new PositiveOrZero(),
                    ],
                ]),
            ]),
        ]);

        dd($data);

        return $this->renderTemplate($this->getConfiguredTemplate($request));
    }

    // ...
}

```
