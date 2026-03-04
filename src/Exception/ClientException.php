<?php

declare(strict_types=1);

namespace ChamberOrchestra\TelegramBundle\Exception;

use Symfony\Component\HttpClient\Exception\HttpExceptionTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class ClientException extends \RuntimeException implements ClientExceptionInterface
{
    use HttpExceptionTrait;
}
