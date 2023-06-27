<?php

declare(strict_types=1);

namespace GalDigitalGmbh\Validation\Symfony;

use GalDigitalGmbh\Validation\Symfony\Exception\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

use function array_keys;
use function array_merge;
use function count;

final class Validator implements ValidatorInterface
{
    private RequestStack $requestStack;

    private ValidatorInterface $validator;

    /**
     * @param mixed[] $constraints
     * @param ParameterBag<mixed>|null $parameters
     *
     * @return mixed[]
     */
    public function validateRequest(array $constraints, ?ParameterBag $parameters = null): array
    {
        $value = $this->getRequestParameters($parameters);

        $this->validate($value, new Collection($constraints, null, null, true));

        return $this->filterValidatedData($constraints, $value);
    }

    /**
     * @param Constraint|Constraint[] $constraints
     * @param string|GroupSequence|array<string|GroupSequence>|null $groups
     */
    public function validate(
        mixed $value,
        Constraint|array $constraints = null,
        string|GroupSequence|array $groups = null,
        bool $throwException = true,
    ): ConstraintViolationListInterface {
        $violations = $this->validator->validate($value, $constraints, $groups);

        return $this->throwViolationsIfNecessary($violations, $throwException);
    }

    /**
     * @param string|GroupSequence|array<string|GroupSequence>|null $groups
     */
    public function validateProperty(
        object $object,
        string $propertyName,
        string|GroupSequence|array $groups = null,
        bool $throwException = true,
    ): ConstraintViolationListInterface {
        $violations = $this->validator->validateProperty($object, $propertyName, $groups);

        return $this->throwViolationsIfNecessary($violations, $throwException);
    }

    /**
     * @param string|GroupSequence|array<string|GroupSequence>|null $groups
     */
    public function validatePropertyValue(
        object|string $objectOrClass,
        string $propertyName,
        mixed $value,
        string|GroupSequence|array $groups = null,
        bool $throwException = true,
    ): ConstraintViolationListInterface {
        $violations = $this->validator->validatePropertyValue($objectOrClass, $propertyName, $value, $groups);

        return $this->throwViolationsIfNecessary($violations, $throwException);
    }

    public function startContext(): ContextualValidatorInterface
    {
        return $this->validator->startContext();
    }

    public function inContext(ExecutionContextInterface $context): ContextualValidatorInterface
    {
        return $this->validator->inContext($context);
    }

    public function getMetadataFor(mixed $value): MetadataInterface
    {
        return $this->validator->getMetadataFor($value);
    }

    public function hasMetadataFor(mixed $value): bool
    {
        return $this->validator->hasMetadataFor($value);
    }

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    #[Required]
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * @param ParameterBag<mixed>|null $parameters
     *
     * @return mixed[]
     */
    private function getRequestParameters(?ParameterBag $parameters = null): array
    {
        if ($parameters) {
            return $parameters->all();
        }

        $request = $this->requestStack->getCurrentRequest() ?? Request::createFromGlobals();

        return array_merge(
            $request->query->all(),
            $request->request->all(),
            $request->files->all(),
            $request->attributes->all(),
        );
    }

    /**
     * @param mixed[] $constraints
     * @param mixed[] $value
     *
     * @return mixed[]
     */
    private function filterValidatedData(array $constraints, array $value): array
    {
        $data = [];

        foreach (array_keys($constraints) as $key) {
            $data[$key] = $value[$key] ?? null;
        }

        return $data;
    }

    private function throwViolationsIfNecessary(
        ConstraintViolationListInterface $violations,
        bool $throwException,
    ): ConstraintViolationListInterface {
        if ($throwException && count($violations)) {
            throw new ValidationException($violations);
        }

        return $violations;
    }
}
