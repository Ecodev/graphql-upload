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
     *
     * @param string $internalRemarks
     */
    public function setInternalRemarks(string $internalRemarks): void
    {
        $this->internalRemarks = $internalRemarks;
    }

    /**
     * Get internalRemarks
     *
     * @return string
     */
    public function getInternalRemarks(): string
    {
        return $this->internalRemarks;
    }
}
