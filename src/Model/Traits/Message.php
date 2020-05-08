<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model\Traits;

use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;

/**
 * A message sent to a user
 */
trait Message
{
    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="MessageType")
     */
    private $type;

    /**
     * @var null|Chronos
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSent;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, options={"default" = ""})
     */
    private $subject = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text", length=65535, options={"default" = ""})
     */
    private $body = '';

    /**
     * Set type
     *
     * @API\Input(type="MessageType")
     *
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @API\Field(type="MessageType")
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Get sent time
     *
     * @return null|Chronos
     */
    public function getDateSent(): ?Chronos
    {
        return $this->dateSent;
    }

    /**
     * Set sent time
     *
     * @API\Exclude
     *
     * @param null|Chronos $dateSent
     */
    public function setDateSent(?Chronos $dateSent): void
    {
        $this->dateSent = $dateSent;
    }

    /**
     * Recipient email address
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Recipient email address
     *
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
