<?php

namespace Nofw\Foundation\Tests\Http\Middleware;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Middlewares\Utils\Delegate;
use Nofw\Foundation\Http\Exception\NotFoundException;
use Nofw\Foundation\Http\Middleware\HttpException;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

final class HttpExceptionTest extends TestCase
{
    /** @var ResponseFactoryInterface|ObjectProphecy */
    private $responseFactory;

    public function setUp()
    {
        $this->responseFactory = $this->prophesize(ResponseFactoryInterface::class);
    }

    /**
     * @test
     */
    public function it_is_a_middleware()
    {
        $middleware = new HttpException($this->responseFactory->reveal());

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    /**
     * @test
     */
    public function it_converts_an_http_exception_to_response()
    {
        $this->responseFactory->createResponse(404)->willReturn(new Response('php://memory', 404));
        $middleware = new HttpException($this->responseFactory->reveal());
        $delegate = new Delegate(function() {
            throw new NotFoundException();
        });

        $response = $middleware->process(new ServerRequest(), $delegate);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_not_convert_a_non_http_exception_to_response()
    {
        $this->expectException(\RuntimeException::class);

        $middleware = new HttpException($this->responseFactory->reveal());
        $delegate = new Delegate(function() {
            throw new \RuntimeException();
        });

        $middleware->process(new ServerRequest(), $delegate);
    }
}
