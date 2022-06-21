<?php

namespace GalDigitalGmbh\Validation\Symfony\Exception;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends RuntimeException
{
    /**
     * @param ConstraintViolationListInterface<mixed> $violations
     */
    public function __construct(
        private ConstraintViolationListInterface $violations,
    ) {
        parent::__construct();
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    /**
     * @return string[][]
     */
    public function getViolationMessages(): array
    {
        $messages = [];

        foreach ($this->getViolations() as $violation) {
            $messages[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $messages;
    }
}
