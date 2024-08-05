<?php

namespace App\Tests\Entity;

use App\Entity\Secret;
use PHPUnit\Framework\TestCase;

class SecretTest extends TestCase
{
    public function testSecretCreation()
    {
        $secret = new Secret();
        $secret->setSecretText('This is a secret');
        $secret->setCreatedAt(new \DateTimeImmutable());
        $secret->setRemainingViews(5);

        $this->assertEquals('This is a secret', $secret->getSecretText());
        $this->assertInstanceOf(\DateTimeImmutable::class, $secret->getCreatedAt());
        $this->assertEquals(5, $secret->getRemainingViews());
    }
}