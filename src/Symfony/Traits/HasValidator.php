<?php

declare(strict_types=1);

namespace GalDigitalGmbh\Validation\Symfony\Traits;

use GalDigitalGmbh\Validation\Symfony\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Service\Attribute\Required;

trait HasValidator
{
    protected Validator $validator;

    #[Required]
    public function setValidator(Validator $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * @param mixed[] $constraints
     *
     * @return mixed[]
     */
    public function validate(array $constraints, ?ParameterBag $parameters = null): array
    {
        return $this->validator->validateRequest($constraints, $parameters);
    }
}
