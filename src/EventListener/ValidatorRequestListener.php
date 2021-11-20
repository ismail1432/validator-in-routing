<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

final class ValidatorRequestListener implements EventSubscriberInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function onKernelRequest(RequestEvent $event = null)
    {
        /** @var Request $request */
        $request = $event->getRequest();
        $validator = $request->get('_validator');

        // Early return if we don't have defined a Validator or
        // the method is not a POST,PUT or GET.
        if (null === $validator || !in_array($request->getMethod(), ['POST', 'PUT', 'GET'])) {
            return;
        }

        // We create a collection of constraint(s) with the constraints given in the defaults Route option.
        $collection = [];
        foreach ($validator as $propertyToValidate => $constraintName) {
            $fqcn = "Symfony\Component\Validator\Constraints\\${constraintName}";
            $constraint = new $fqcn;

            if (!$constraint instanceof Constraint) {
                throw new \LogicException("Only Constraint are allowed to validate value");
            }

            $collection[$propertyToValidate] = new $constraint;
        }

        // If method is POST or PUT, we should get the payload from the request content
        // otherwise we get it from query string
        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $payload = \json_decode($request->getContent(), true);
        } else {
            // Retrieve the input from the query string.
            $payload = $request->query->all();
        }

        // We remove properties that should not have to be validated
        $input = array_intersect_key($payload, $collection);

        $errors = $this->validator->validate($input, new Constraints\Collection($collection));

        // We set errors in a key _errors, we can retrieve them from
        // the request with $request->attributes->get('_errors');
        $request->attributes->set('_errors', $errors);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 31]],
        ];
    }

}