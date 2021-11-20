<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserActionTest extends WebTestCase
{
    public function testWrongPayload(): void
    {
        $client = static::createClient();
        $payload = [
            "email" => "FR_fr",
            "name" => null
        ];

        $client->request('POST', '/users', [], [], [], json_encode($payload));
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(400);

        $expected = [
            [
                "property" => "[name]",
                "message" => "This value should not be blank."
            ],
            [
                "property" => "[email]",
                "message" => "This value is not a valid email address."
            ]
        ];

        self::assertEquals($expected, $response);
    }

    public function testWrongLimitCriteria(): void
    {
        $client = static::createClient();
        $client->request('GET', '/users?limit=-42');
        $response = json_decode($client->getResponse()->getContent(), true);

        $expected = [
            [
                "property" => "[limit]",
                'message' => 'This value should be positive.'
            ]
        ];
        self::assertEquals($expected, $response);
    }
}
