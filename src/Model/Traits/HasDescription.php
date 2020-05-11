<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model\Traits;

trait HasDescription
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $description = '';

    /**
     * Set description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get description
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
