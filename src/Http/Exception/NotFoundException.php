<?php

declare(strict_types=1);

namespace Nofw\Foundation\Http\Exception;

use Nofw\Foundation\Http\Exception;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class NotFoundException extends \Exception implements Exception
{
    public function getStatusCode(): int
    {
        return 404;
    }
}
