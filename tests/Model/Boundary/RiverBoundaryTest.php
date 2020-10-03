<?php

declare(strict_types=1);

namespace App\Tests\Model\Boundary;

use App\Model\Modflow\Boundary\BoundaryFactory;
use App\Model\Modflow\Boundary\RiverBoundary;
use Exception;
use GeoJson\Feature\Feature;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Swaggest\JsonSchema\Schema;

class RiverBoundaryTest extends TestCase
{

    private array $riverBoundaryJson;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->riverBoundaryJson = [
            'type' => "FeatureCollection",
            'features' => [
                [
                    'type' => 'Feature',
                    'id' => Uuid::uuid4()->toString(),
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => [[125.6, 10.1], [125.7, 10.2], [125.8, 10.3]]
                    ],
                    'properties' => [
                        'type' => 'riv',
                        'name' => 'My new River',
                        'layers' => [1],
                        'cells' => [[3, 4], [4, 5]],
                    ]
                ],
                [
                    'type' => 'Feature',
                    'id' => 'op1',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [125.6, 10.1]
                    ],
                    'properties' => [
                        'type' => 'op',
                        'name' => 'OP1',
                        'sp_values' => [
                            [1, 2, 3]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_validates_the_river_boundary_schema_successfully(): void
    {
        $schema = 'https://schema.inowas.com/modflow/boundary/riverBoundary.json';
        $schema = Schema::import($schema);
        $object = json_decode(json_encode($this->riverBoundaryJson, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);

        $riverBoundary = BoundaryFactory::fromArray($this->riverBoundaryJson);
        $object = json_decode(json_encode($riverBoundary->jsonSerialize(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_creates_a_river_boundary_from_json(): void
    {
        /** @var RiverBoundary $riverBoundary */
        $riverBoundary = BoundaryFactory::fromArray($this->riverBoundaryJson);
        self::assertInstanceOf(RiverBoundary::class, $riverBoundary);
        self::assertInstanceOf(Feature::class, $riverBoundary->river());
        self::assertCount(1, $riverBoundary->observationPoints());
        self::assertEquals($this->riverBoundaryJson, $riverBoundary->jsonSerialize());
    }
}
