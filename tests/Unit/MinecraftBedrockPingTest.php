<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Integrations\MinecraftBedrockPing;

class MinecraftBedrockPingTest extends TestCase
{
    public function testBuildPingPacketStartsWithCorrectId(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $buildPingPacket = $reflection->getMethod('buildPingPacket');
        $buildPingPacket->setAccessible(true);

        $packet = $buildPingPacket->invoke($instance);

        $this->assertEquals("\x01", $packet[0]);
    }

    public function testBuildPingPacketIncludesRakNetMagic(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $buildPingPacket = $reflection->getMethod('buildPingPacket');
        $buildPingPacket->setAccessible(true);

        $packet = $buildPingPacket->invoke($instance);

        $magic = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
        $this->assertStringContainsString($magic, $packet);
    }

    public function testBuildPingPacketHasCorrectLength(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $buildPingPacket = $reflection->getMethod('buildPingPacket');
        $buildPingPacket->setAccessible(true);

        $packet = $buildPingPacket->invoke($instance);

        $this->assertEquals(33, strlen($packet));
    }

    public function testParseValidBedrockPongPayload(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseServerIdString = $reflection->getMethod('parseServerIdString');
        $parseServerIdString->setAccessible(true);

        $serverId = 'MCPE;Dedicated Server;776;1.21.0;10;20;12345678;Bedrock level;Survival;1;19132;19133;';

        $result = $parseServerIdString->invoke($instance, $serverId);

        $this->assertNotFalse($result);
        $this->assertTrue($result['online']);
        $this->assertEquals(10, $result['players']);
        $this->assertEquals(20, $result['max_players']);
        $this->assertEquals('1.21.0', $result['version']);
        $this->assertEquals(776, $result['protocol_version']);
        $this->assertEquals('Dedicated Server', $result['motd']);
        $this->assertEquals('Bedrock level', $result['motd_extra']);
        $this->assertEquals('12345678', $result['server_guid']);
        $this->assertEquals('Survival', $result['game_mode']);
        $this->assertEquals(1, $result['game_mode_numeric']);
        $this->assertEquals(19132, $result['ipv4_port']);
        $this->assertEquals(19133, $result['ipv6_port']);
        $this->assertEquals('MCPE', $result['edition']);
    }

    public function testParseBedrockPongWithMinimalFields(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseServerIdString = $reflection->getMethod('parseServerIdString');
        $parseServerIdString->setAccessible(true);

        $serverId = 'MCPE;My Server;503;1.19.0;5;25;abcd1234;;;;;';

        $result = $parseServerIdString->invoke($instance, $serverId);

        $this->assertNotFalse($result);
        $this->assertTrue($result['online']);
        $this->assertEquals(5, $result['players']);
        $this->assertEquals(25, $result['max_players']);
        $this->assertEquals('1.19.0', $result['version']);
        $this->assertEquals(503, $result['protocol_version']);
        $this->assertEquals('My Server', $result['motd']);
    }

    public function testRejectsNonMCPEEdition(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseServerIdString = $reflection->getMethod('parseServerIdString');
        $parseServerIdString->setAccessible(true);

        $result = $parseServerIdString->invoke($instance, 'MCEE;Education Edition;1;1.0;0;0;;;;;;');

        $this->assertFalse($result);
    }

    public function testRejectsEmptyPayload(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseServerIdString = $reflection->getMethod('parseServerIdString');
        $parseServerIdString->setAccessible(true);

        $result = $parseServerIdString->invoke($instance, '');

        $this->assertFalse($result);
    }

    public function testRejectsNonMCPEStart(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseServerIdString = $reflection->getMethod('parseServerIdString');
        $parseServerIdString->setAccessible(true);

        $result = $parseServerIdString->invoke($instance, 'MINECRAFT;Test;1;1.0;0;0;;;;;;');

        $this->assertFalse($result);
    }

    public function testParseResponseRejectsWrongPacketId(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseResponse = $reflection->getMethod('parseResponse');
        $parseResponse->setAccessible(true);

        $magic = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";

        $wrongId = "\x00"
            . pack('P', 0)
            . pack('P', 0)
            . $magic
            . pack('n', 4)
            . 'test';

        $result = $parseResponse->invoke($instance, $wrongId);

        $this->assertFalse($result);
    }

    public function testParseResponseRejectsMissingMagic(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseResponse = $reflection->getMethod('parseResponse');
        $parseResponse->setAccessible(true);

        $badMagic = str_repeat("\x00", 16);

        $invalid = "\x1c"
            . pack('P', 0)
            . pack('P', 0)
            . $badMagic
            . pack('n', 4)
            . 'test';

        $result = $parseResponse->invoke($instance, $invalid);

        $this->assertFalse($result);
    }

    public function testParseResponseHandlesValidPong(): void
    {
        $reflection = new \ReflectionClass(MinecraftBedrockPing::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $parseResponse = $reflection->getMethod('parseResponse');
        $parseResponse->setAccessible(true);

        $magic = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
        $serverId = 'MCPE;Test Server;776;1.21.0;15;30;guid123;Extra;Creative;2;19132;19133;';

        $valid = "\x1c"
            . pack('P', 0)
            . pack('P', 12345)
            . $magic
            . pack('n', strlen($serverId))
            . $serverId;

        $result = $parseResponse->invoke($instance, $valid);

        $this->assertNotFalse($result);
        $this->assertEquals(15, $result['players']);
        $this->assertEquals(30, $result['max_players']);
        $this->assertEquals('1.21.0', $result['version']);
        $this->assertEquals('Test Server', $result['motd']);
        $this->assertEquals('Creative', $result['game_mode']);
    }
}
