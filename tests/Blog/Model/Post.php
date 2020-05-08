<?php

declare(strict_types=1);

namespace EcodevTests\Felix\Blog\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * A blog post with title and body
 *
 * @ORM\Entity(repositoryClass="EcodevTests\Felix\Blog\Repository\PostRepository")
 */
final class Post extends AbstractModel
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, options={"default" = ""})
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $body = '';

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="EcodevTests\Felix\Blog\Model\User", inversedBy="posts")
     */
    private $user;
}
