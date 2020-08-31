<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Command\Redis;

/**
 * @group commands
 * @group realm-hash
 */
class HINCRBYFLOAT_Test extends PredisCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedCommand()
    {
        return 'Predis\Command\Redis\HINCRBYFLOAT';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedId()
    {
        return 'HINCRBYFLOAT';
    }

    /**
     * @group disconnected
     */
    public function testFilterArguments()
    {
        $arguments = array('key', 'field', 10.5);
        $expected = array('key', 'field', 10.5);

        $command = $this->getCommand();
        $command->setArguments($arguments);

        $this->assertSame($expected, $command->getArguments());
    }

    /**
     * @group disconnected
     */
    public function testParseResponse()
    {
        $this->assertSame(10.5, $this->getCommand()->parseResponse(10.5));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testIncrementsValueOfFieldByFloat()
    {
        $redis = $this->getClient();

        $this->assertSame('10.5', $redis->hincrbyfloat('metavars', 'foo', 10.5));

        $redis->hincrbyfloat('metavars', 'hoge', 10.001);
        $this->assertSame('11', $redis->hincrbyfloat('metavars', 'hoge', 0.999));

        $this->assertSame(array('foo' => '10.5', 'hoge' => '11'), $redis->hgetall('metavars'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testDecrementsValueOfFieldByFloat()
    {
        $redis = $this->getClient();

        $this->assertSame('-10.5', $redis->hincrbyfloat('metavars', 'foo', -10.5));

        $redis->hincrbyfloat('metavars', 'hoge', -10.001);
        $this->assertSame('-11', $redis->hincrbyfloat('metavars', 'hoge', -0.999));

        $this->assertSame(array('foo' => '-10.5', 'hoge' => '-11'), $redis->hgetall('metavars'));
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testThrowsExceptionOnStringField()
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessageMatches('/ERR hash value is not a( valid)? float/');

        $redis = $this->getClient();

        $redis->hset('metavars', 'foo', 'bar');
        $redis->hincrbyfloat('metavars', 'foo', 10.0);
    }

    /**
     * @group connected
     * @requiresRedisVersion >= 2.6.0
     */
    public function testThrowsExceptionOnWrongType()
    {
        $this->expectException('Predis\Response\ServerException');
        $this->expectExceptionMessage('Operation against a key holding the wrong kind of value');

        $redis = $this->getClient();

        $redis->set('foo', 'bar');
        $redis->hincrbyfloat('foo', 'bar', 10.5);
    }
}