<?php

declare(strict_types=1);

namespace Nofw\Foundation\Http;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface Exception
{
    public function getStatusCode(): int;
}
