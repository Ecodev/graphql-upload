<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model\Traits;

trait HasInternalRemarks
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $internalRemarks = '';

    /**
     * Set internalRemarks
     */
    public function setInternalRemarks(string $internalRemarks): void
    {
        $this->internalRemarks = $internalRemarks;
    }

    /**
     * Get internalRemarks
     */
    public function getInternalRemarks(): string
    {
        return $this->internalRemarks;
    }
}
