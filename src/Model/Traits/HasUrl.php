<?php

declare(strict_types=1);

namespace Ecodev\Felix\Model\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for all objects with an URL
 */
trait HasUrl
{
    /**
     * @var string
     * @ORM\Column(type="string", length=2000, options={"default" = ""})
     */
    private $url = '';

    /**
     * Set url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Get url
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
