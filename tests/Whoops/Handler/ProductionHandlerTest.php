<?php

namespace Nofw\Foundation\Tests\Whoops\Handler;

use Nofw\Foundation\Whoops\Handler\ProductionHandler;
use PHPUnit\Framework\TestCase;
use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;

final class ProductionHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_handler()
    {
        $handler  = new ProductionHandler(true);

        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }

    /**
     * @test
     */
    public function it_is_skipped_when_debug_is_enabled()
    {
        $handler  = new ProductionHandler(true);

        $result = $handler->handle();

        $this->assertEquals(Handler::DONE, $result);
    }

    /**
     * @test
     */
    public function it_quits_if_the_application_debug_is_disabled()
    {
        $handler  = new ProductionHandler(false);

        $result = $handler->handle();

        $this->assertEquals(Handler::QUIT, $result);
    }
}
