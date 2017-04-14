<?php

declare(strict_types=1);

namespace Nofw\Foundation\Http\Exception;

use Nofw\Foundation\Http\Exception;

final class NotFoundException extends \Exception implements Exception
{
    public function getStatusCode(): int
    {
        return 404;
    }
}
