<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Blog\Model;

use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;

/**
 * A blog author
 *
 * @ORM\Entity
 */
final class User extends AbstractModel
{
    /**
     * @var string
     *
     * @ORM\Column(name="custom_column_name", type="string", length=50, options={"default" = ""})
     */
    private $name = '';

    /**
     * @var null|string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     * @Api\Exclude
     */
    private $password;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="EcodevTests\Felix\Blog\Model\Post", mappedBy="user")
     */
    private $posts;
}
