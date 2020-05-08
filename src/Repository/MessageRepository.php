<?php

declare(strict_types=1);

namespace Ecodev\Felix\Repository;

use Ecodev\Felix\Model\Message;

interface MessageRepository
{
    /**
     * @return Message[]
     */
    public function getAllMessageToSend(): array;
}
