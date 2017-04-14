<?php

declare(strict_types=1);

namespace Nofw\Foundation\Http;

interface Exception
{
    public function getStatusCode(): int;
}
