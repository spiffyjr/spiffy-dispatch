<?php

namespace Spiffy\Dispatch;

/**
 * @coversDefaultClass \Spiffy\Dispatch\Dispatcher
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::add, \Spiffy\Dispatch\Exception\DispatchableExistsException::__construct
     * @expectedException \Spiffy\Dispatch\Exception\DispatchableExistsException
     * @expectedExceptionMessage The dispatchable with name "foo" already exists
     */
    public function testAddThrowsExceptionOnDuplicate()
    {
        $d = new Dispatcher();
        $d->add('foo', function() {});
        $d->add('foo', function() {});
    }

    /**
     * @covers ::add
     */
    public function testAddSetsInstance()
    {
        $d = new Dispatcher();
        $d->add('foo', 'foo::baz');
        $d->add('bar', 'bar::baz');

        $refl = new \ReflectionClass($d);
        $instances = $refl->getProperty('instances');
        $instances->setAccessible(true);
        $instances = $instances->getValue($d);

        $this->assertCount(2, $instances);
        $this->assertSame('foo::baz', $instances['foo']);
        $this->assertSame('bar::baz', $instances['bar']);
    }

    /**
     * @covers ::dispatch, \Spiffy\Dispatch\Exception\MissingDispatchableException::__construct
     * @expectedException \Spiffy\Dispatch\Exception\MissingDispatchableException
     * @expectedExceptionMessage The dispatchable with name "foo" does not exist
     */
    public function testDispatchThrowsExceptionForMissingDispatchable()
    {
        $d = new Dispatcher();
        $d->ispatch('foo');
    }

    /**
     * @covers ::dispatch, ::buildArgsFromReflectionFunction, \Spiffy\Dispatch\Exception\MissingRequiredArgumentException::__construct
     * @expectedException \Spiffy\Dispatch\Exception\MissingRequiredArgumentException
     * @expectedExceptionMessage Dispatchable failed to dispatch: missing required parameter "id"
     */
    public function testDispatchThrowsExceptionForMissingParameters()
    {
        $d = new Dispatcher();
        $d->add('foo', function($id) {});
        $d->ispatch('foo');
    }

    /**
     * @covers ::dispatch, ::dispatchObject, ::dispatchClosure, ::buildArgsFromReflectionFunction
     */
    public function testDispatchHandlesClosures()
    {
        $d = new Dispatcher();
        $d->add('foo', function($id, $slug = 'foo') {
            return 'id: ' . $id . ', slug: ' . $slug;
        });

        $this->assertSame('id: 1, slug: foo', $d->ispatch('foo', ['id' => 1]));
        $this->assertSame('id: 1, slug: bar', $d->ispatch('foo', ['id' => 1, 'slug' => 'bar']));
    }

    /**
     * @covers ::dispatch, ::dispatchObject, ::dispatchDispatchable, ::buildArgsFromReflectionFunction
     */
    public function testDispatchHandlesDispatchables()
    {
        $d = new Dispatcher();
        $d->add('foo', new TestAsset\TestDispatchable());
        $params = ['id' => 1, 'slug' => 'bar'];

        $this->assertSame($params, $d->ispatch('foo', $params));
    }

    /**
     * @covers ::dispatch, ::dispatchObject, ::dispatchInvokable, ::buildArgsFromReflectionFunction
     */
    public function testDispatchHandlesInvokables()
    {
        $d = new Dispatcher();
        $d->add('foo', new TestAsset\Invokable());

        $this->assertSame('id: 1, slug: foo', $d->ispatch('foo', ['id' => 1]));
        $this->assertSame('id: 1, slug: bar', $d->ispatch('foo', ['id' => 1, 'slug' => 'bar']));
    }

    /**
     * @covers ::dispatch, ::dispatchObject, ::dispatchCallable, ::buildArgsFromReflectionFunction
     */
    public function testDispatchHandlesCallables()
    {
        $d = new Dispatcher();
        $d->add('foo', '\Spiffy\Dispatch\DispatcherTest::callableFunction');
        $this->assertSame('id: 1, slug: foo', $d->ispatch('foo', ['id' => 1]));
        $this->assertSame('id: 1, slug: bar', $d->ispatch('foo', ['id' => 1, 'slug' => 'bar']));

        $d->add('foo2', [$this, 'callableFunction']);
        $this->assertSame('id: 1, slug: foo', $d->ispatch('foo2', ['id' => 1]));
        $this->assertSame('id: 1, slug: bar', $d->ispatch('foo2', ['id' => 1, 'slug' => 'bar']));
    }

    /**
     * @covers ::ispatch, ::dispatch
     */
    public function testIspatchProxiesToDispatch()
    {
        $result = new \StdClass();

        $d = new Dispatcher();
        $d->add('foo', function() use ($result) { return $result; });

        $this->assertSame($d->ispatch('foo'), $d->dispatch('foo'));
    }

    /**
     * @covers ::dispatch
     */
    public function testDispatchReturnsNonDispatchables()
    {
        $d = new Dispatcher();
        $d->add('foo', true);

        $this->assertTrue($d->dispatch('foo'));
    }

    /**
     * @covers ::dispatch
     */
    public function testDispatchRecurses()
    {
        $d = new Dispatcher();
        $d->add('foo', function() use ($d) {
            return 'foo';
        });
        $d->add('bar', function() use ($d) {
            return $d->ispatch('foo') . 'bar';
        });
        $d->add('baz', function() use ($d) {
            return $d->ispatch('bar') . 'baz';
        });

        $this->assertSame('foobarbaz', $d->ispatch('baz'));
    }

    public static function callableFunction($id, $slug = 'foo')
    {
        return 'id: ' . $id . ', slug: ' . $slug;
    }
}
