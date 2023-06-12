<?php

namespace Palzin\Symfony\Bundle\Tests\Integration;

use Palzin\Palzin;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IntegrationTest extends KernelTestCase
{
    public function testServiceWiring()
    {
        self::bootKernel();

        $palzinService = static::getContainer()->get(Palzin::class);

        $this->assertInstanceOf(Palzin::class, $palzinService);
    }

    public function testServiceWiringWithConfiguration()
    {
        self::bootKernel(['environment' => 'test']);

        $palzinService = static::getContainer()->get(Palzin::class);

        $this->assertFalse($palzinService->hasTransaction());
    }
}

