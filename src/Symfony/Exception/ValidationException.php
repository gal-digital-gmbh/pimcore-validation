<?php

declare(strict_types=1);

namespace GalDigitalGmbh\Validation\Symfony\Exception;

use RuntimeException;
use Stringable;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends RuntimeException
{
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
            $message = $violation->getMessage();

            $messages[$violation->getPropertyPath()][] = $message instanceof Stringable
                ? $message->__toString()
                : $message;
        }

        return $messages;
    }
}
