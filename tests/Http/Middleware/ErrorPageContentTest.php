<?php

namespace Nofw\Foundation\Tests\Http\Middleware;

use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Middlewares\Utils\Delegate;
use Nofw\Foundation\Http\Middleware\ErrorPageContent;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

final class ErrorPageContentTest extends TestCase
{
    /** @var \Twig_Environment|ObjectProphecy */
    private $twig;

    /** @var StreamFactoryInterface|ObjectProphecy */
    private $streamFactory;

    public function setUp()
    {
        $this->twig = $this->prophesize(\Twig_Environment::class);
        $this->streamFactory = $this->prophesize(StreamFactoryInterface::class);
    }

    /**
     * @test
     */
    public function it_is_a_middleware()
    {
        $middleware = new ErrorPageContent(
            $this->twig->reveal(),
            $this->streamFactory->reveal()
        );

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    /**
     * @test
     */
    public function it_does_not_modify_a_successful_request()
    {
        $middleware = new ErrorPageContent(
            $this->twig->reveal(),
            $this->streamFactory->reveal()
        );

        $response = new Response('php://memory', 200);
        $delegate = new Delegate(function() use ($response) {
            return $response;
        });

        $returnedResponse = $middleware->process(new ServerRequest(), $delegate);

        $this->assertSame($response, $returnedResponse);
    }

    /**
     * @dataProvider errorProvider
     * @test
     */
    public function it_creates_a_body_for_empty_error_responses($statusCode, $template, $context, $body)
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write($body);
        $this->streamFactory->createStream($body)->willReturn($stream);

        $middleware = new ErrorPageContent(
            $this->twig->reveal(),
            $this->streamFactory->reveal()
        );

        $delegate = new Delegate(function() use ($statusCode) {
            return new Response('php://memory', $statusCode);
        });

        if (is_array($context)) {
            $this->twig->render($template, $context)->willReturn($body);
        } else {
            $this->twig->render($template)->willReturn($body);
        }

        $response = $middleware->process((new ServerRequest())->withHeader('Accept', 'text/html'), $delegate);

        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($body, (string) $response->getBody());
    }

    public function errorProvider(): array
    {
        return [
            [404, 'error/error404.html.twig', null, '404'],
            [
                500,
                'error/error.html.twig',
                [
                    'status_code' => 500,
                    'reason_phrase' => 'Internal Server Error',
                ],
                'error',
            ],
        ];
    }
}
