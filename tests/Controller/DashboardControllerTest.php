<?php

namespace App\Tests\Controller;

use JsonException;

class DashboardControllerTest extends CommandTestBaseClass
{
    /**
     * @test
     * @throws JsonException
     */
    public function aUserCanReadHisPrivateTools(): void
    {
        $user = $this->createRandomUser();
        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $privateTool = $this->createSimpleTool($user, false);

        $response = $this->sendRequest('/v3/tools/' . $privateTool->tool(), $token);
        self::assertEquals(200, $response->getStatusCode());
        $tools = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $tools);
        self::assertEquals($privateTool->toArray()['id'], $tools[0]['id']);
    }

    /**
     * @test
     * @throws JsonException
     */
    public function aUserCanReadAllPublicTools(): void
    {
        $user1 = $this->createRandomUser();
        $publicTool1User1 = $this->createSimpleTool($user1, true);
        $this->createSimpleTool($user1, true);
        $this->createSimpleTool($user1, false);

        $user2 = $this->createRandomUser();
        $this->createSimpleTool($user2, true);
        $this->createSimpleTool($user2, true);
        $token = $this->getToken($user2->getUsername(), $user2->getPassword());

        $response = $this->sendRequest('/v3/tools/' . $publicTool1User1->tool() . '/?public=true', $token);
        self::assertEquals(200, $response->getStatusCode());
        $tools = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(4, $tools);
    }

    /**
     * @test
     * @throws JsonException
     */
    public function aUserCanReadAllPublicModflowModels(): void
    {
        $user = $this->createRandomUser();
        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendRequest('/v3/tools/T03?public=true', $token);
        self::assertEquals(200, $response->getStatusCode());
        $tools = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(0, $tools);
    }
}
