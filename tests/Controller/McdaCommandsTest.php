<?php

namespace App\Tests\Controller;

use App\Model\Mcda\Mcda;
use Exception;
use Ramsey\Uuid\Uuid;

class McdaCommandsTest extends CommandTestBaseClass
{
    /**
     * @test
     * @throws Exception
     */
    public function sendCreateToolInstanceCommand(): void
    {
        $user = $this->createRandomUser();
        $mcdaId = Uuid::uuid4()->toString();

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'createToolInstance',
            'metadata' => (object)[],
            'payload' => [
                'tool' => 'T05',
                'id' => $mcdaId,
                'name' => 'New Mcda',
                'description' => 'This Mcda description',
                'public' => true,
                'data' => [
                    'criteria' => ['abc' => 'def'],
                    'weight_assignments' => ['ghi' => 'jkl'],
                    'constraints' => ['mno' => 'pqr'],
                    'with_ahp' => true,
                    'suitability' => ['stu' => 'vwx']
                ]
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var Mcda $mcda */
        $mcda = $this->em->getRepository(Mcda::class)->findOneBy(['id' => $mcdaId]);
        self::assertInstanceOf(Mcda::class, $mcda);
        self::assertEquals($command['payload']['tool'], $mcda->tool());
        self::assertEquals($command['payload']['name'], $mcda->name());
        self::assertEquals($command['payload']['description'], $mcda->description());
        self::assertEquals($command['payload']['public'], $mcda->isPublic());
        self::assertEquals($user->getId()->toString(), $mcda->getUser()->getId()->toString());

        self::assertEquals($command['payload']['data']['criteria'], $mcda->critera());
        self::assertEquals($command['payload']['data']['constraints'], $mcda->constraints());
        self::assertEquals($command['payload']['data']['suitability'], $mcda->suitability());
        self::assertEquals($command['payload']['data']['weight_assignments'], $mcda->weightAssignments());
        self::assertEquals($command['payload']['data']['with_ahp'], $mcda->withAhp());
    }
}
