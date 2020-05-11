<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

use Cake\Chronos\Chronos;

interface Message extends Model
{
    public function getSubject(): string;

    public function setSubject(string $subject): void;

    public function getBody(): string;

    public function setBody(string $body): void;

    public function getDateSent(): ?Chronos;

    /**
     * Set sent time
     *
     * @API\Exclude
     */
    public function setDateSent(?Chronos $dateSent): void;

    /**
     * Recipient email address
     */
    public function getEmail(): string;

    /**
     * Recipient email address
     */
    public function setEmail(string $email): void;

    /**
     * Get recipient
     */
    public function getRecipient(): ?User;

    /**
     * Get type
     */
    public function getType(): string;
}
