<?php

declare(strict_types=1);

namespace Ecodev\Felix\Acl;

use Doctrine\Common\Util\ClassUtils;
use Ecodev\Felix\Model\Model;
use Ecodev\Felix\Utility;
use InvalidArgumentException;
use Laminas\Permissions\Acl\Resource\GenericResource;

/**
 * An ACL resource linked to a specific instance of a Model
 *
 * Usage:
 *
 *     $r = new ModelResource(Question::class, $question);
 *     $question = $r->getInstance();
 */
final class ModelResource extends GenericResource
{
    /**
     * Unique id of the instance of resource
     *
     * @var null|Model
     */
    private $instance;

    /**
     * Sets the Resource identifier
     *
     * @param string $class must be a model class name
     * @param Model $instance the instance itself
     */
    public function __construct(string $class, ?Model $instance = null)
    {
        if (!is_subclass_of($class, Model::class)) {
            throw new InvalidArgumentException('The class name must be an implementation of Model but given: ' . $class);
        }

        $class = ClassUtils::getRealClass($class);

        parent::__construct($class);
        $this->instance = $instance;
    }

    /**
     * Returns the specific instance of resource found by its type and id.
     */
    public function getInstance(): ?Model
    {
        return $this->instance;
    }

    /**
     * Returns a name identifying this resource for exception messages for developers
     */
    public function getName(): string
    {
        $instance = $this->getInstance();

        return Utility::getShortClassName($this->resourceId) . '#' . ($instance ? $instance->getId() ?? 'null' : 'null');
    }
}
