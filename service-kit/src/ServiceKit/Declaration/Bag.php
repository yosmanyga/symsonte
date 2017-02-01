<?php

namespace Symsonte\ServiceKit\Declaration;

use Symsonte\ServiceKit\Declaration;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 */
class Bag
{
    /**
     * @var Declaration[]
     */
    private $declarations;

    /**
     * @var string[]
     */
    private $parameters;

    /**
     * @param Declaration[]|null $declarations
     * @param string[]|null      $parameters
     */
    public function __construct(
        array $declarations = null,
        array $parameters = null
    )
    {
        $this->declarations = $declarations ?: [];
        $this->parameters = $parameters ?: [];
    }

    /**
     * @return Declaration[]
     */
    public function getDeclarations()
    {
        return $this->declarations;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasDeclaration($id)
    {
        return isset($this->declarations[$id]);
    }

    /**
     * @param string $id
     *
     * @return Declaration
     *
     * @throws \Exception
     */
    public function getDeclaration($id)
    {
        if (!$this->hasDeclaration($id)) {
            throw new \Exception();
        }

        return $this->declarations[$id];
    }

    /**
     * @return string[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}