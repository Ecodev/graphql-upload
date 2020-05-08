<?php

declare(strict_types=1);

namespace Ecodev\Felix\Repository\Traits;

use Ecodev\Felix\Model\Message;

trait MessageRepository
{
    /**
     * @return Message[]
     */
    public function getAllMessageToSend(): array
    {
        $qb = $this->createQueryBuilder('message')
            ->where('message.dateSent IS NULL')
            ->addOrderBy('message.id');

        return $qb->getQuery()->setMaxResults(500)->getResult();
    }
}
