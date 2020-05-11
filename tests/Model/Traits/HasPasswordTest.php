<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Model\Traits;

use Cake\Chronos\Chronos;
use Ecodev\Felix\Model\Traits\HasPassword;
use PHPUnit\Framework\TestCase;

final class HasPasswordTest extends TestCase
{
    /**
     * @var \Ecodev\Felix\Model\HasPassword
     */
    private $user;

    protected function setUp(): void
    {
        $this->user = new class() implements \Ecodev\Felix\Model\HasPassword {
            use HasPassword;
        };
    }

    public function testSetPassword(): void
    {
        self::assertSame('', $this->user->getPassword(), 'should have no password at first');

        $this->user->setPassword('12345');
        $actual1 = $this->user->getPassword();
        self::assertNotSame('', $actual1, 'should be able to change password ');
        self::assertTrue(password_verify('12345', $actual1), 'password must have been hashed');

        $this->user->setPassword('');
        $actual2 = $this->user->getPassword();
        self::assertSame($actual1, $actual2, 'should ignore empty password');

        $this->user->setPassword('money');
        $actual3 = $this->user->getPassword();
        self::assertNotSame($actual1, $actual3, 'should be able to change to something else');
        self::assertTrue(password_verify('money', $actual3), 'password must have been hashed again');
    }

    public function testToken(): void
    {
        self::assertFalse($this->user->isTokenValid(), 'new user should not be valid');

        $token1 = $this->user->createToken();
        self::assertEquals(32, mb_strlen($token1), 'must be exactly the length of DB field');
        self::assertTrue($this->user->isTokenValid(), 'brand new token is valid');

        $token2 = $this->user->createToken();
        self::assertEquals(32, mb_strlen($token2), 'must be exactly the length of DB field');
        self::assertTrue($this->user->isTokenValid(), 'second created token is valid');

        $this->user->revokeToken();
        self::assertFalse($this->user->isTokenValid(), 'once user is logged in token is invalid');

        $token3 = $this->user->createToken();
        self::assertEquals(32, mb_strlen($token3), 'must be exactly the length of DB field');
        self::assertTrue($this->user->isTokenValid(), 'third created token is valid');

        $token4 = $this->user->createToken();
        self::assertEquals(32, mb_strlen($token4), 'must be exactly the length of DB field');
        self::assertTrue($this->user->isTokenValid(), 'third created token is valid');

        $this->user->setPassword('money');
        self::assertFalse($this->user->isTokenValid(), 'after password change token is invalid');

        Chronos::setTestNow((new Chronos())->subDay(1));
        $token5 = $this->user->createToken();
        Chronos::setTestNow(null);
        self::assertEquals(32, mb_strlen($token5), 'must be exactly the length of DB field');
        self::assertFalse($this->user->isTokenValid(), 'too old token is invalid');

        $allTokens = [
            $token1,
            $token2,
            $token3,
            $token4,
            $token5,
        ];

        self::assertCount(5, array_unique($allTokens), 'all tokens must be unique');
    }
}
