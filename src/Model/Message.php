<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model;

use Cake\Chronos\Chronos;

interface Message extends Model
{
    /**
     * @return string
     */
    public function getSubject(): string;

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void;

    /**
     * @return string
     */
    public function getBody(): string;

    /**
     * @param string $body
     */
    public function setBody(string $body): void;

    /**
     * Get sent time
     *
     * @return null|Chronos
     */
    public function getDateSent(): ?Chronos;

    /**
     * Set sent time
     *
     * @API\Exclude
     *
     * @param null|Chronos $dateSent
     */
    public function setDateSent(?Chronos $dateSent): void;

    /**
     * Recipient email address
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Recipient email address
     *
     * @param string $email
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
