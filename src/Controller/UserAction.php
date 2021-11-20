<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationList;

class UserAction
{
    /**
     * @Route("/users",
     *       name="create",
     *       methods="POST",
     *       defaults={
     *          "_validator": {
     *              "name":"NotBlank",
     *              "email":"Email"
     *       }
     * })
     */
    public function create(Request $request): JsonResponse
    {
        $errors = $request->attributes->get('_errors');

        if ([] !== $errors) {
            return new JsonResponse($this->formatError($errors), 400);
        }

        return new JsonResponse(["blabla"], 201);
    }

    /**
     * @Route("/users",
     *       name="get",
     *       methods="GET",
     *       defaults={
     *          "_validator": {
     *              "limit":"Positive"
     *       }
     * })
     */
    public function search(Request $request): Response
    {
        $errors = $request->attributes->get('_errors');

        if ([] !== $errors) {
            return new JsonResponse($this->formatError($errors), 400);

        }
        // boring stuff, fetch users...
        return new JsonResponse(["blabla"], 201);
    }

    private function formatError(ConstraintViolationList $constraintViolationList): array
    {
        $format = [];

        foreach ($constraintViolationList as $violation) {
            $format[] = [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage()
            ];
        }

        return $format;
    }
}
