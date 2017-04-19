<?php

namespace Nofw\Foundation\Http\Middleware;

use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class ErrorPageContent implements MiddlewareInterface
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(\Twig_Environment $twig, StreamFactoryInterface $streamFactory)
    {
        $this->twig = $twig;
        $this->streamFactory = $streamFactory;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);

        // If there is an error, but there is no body
        if ($this->isError($response) && $response->getBody()->getSize() < 1) {
            if ($this->isHtml($request, $response)) {
                switch ($response->getStatusCode()) {
                    case 404:
                        $html = $this->twig->render('error/error404.html.twig');
                        break;

                    default:
                        $html = $this->twig->render('error/error.html.twig', [
                            'status_code' => $response->getStatusCode(),
                            'reason_phrase' => $response->getReasonPhrase(),
                        ]);
                        break;
                }

                $body = $this->streamFactory->createStream($html);
                $body->rewind();

                return $response->withBody($body);
            }
        }

        return $response;
    }

    /**
     * Checks if the response is an error one based on the status code.
     */
    private function isError(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 600;
    }

    /**
     * Checks if HTML response is expected by the client.
     */
    private function isHtml(ServerRequestInterface $request, ResponseInterface $response): bool
    {
        $accept = $request->getHeaderLine('Accept');
        $contentType = $response->getHeaderLine('Content-Type');

        // TODO: improve negotiation
        return empty($accept) ||
            stripos($accept, '*') !== false ||
            stripos($accept, 'text/html') !== false ||
            stripos($contentType, 'text/html') !== false
        ;
    }
}
