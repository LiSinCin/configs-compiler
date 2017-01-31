<?php

namespace Sli\ConfigsCompiler;

use Sli\ConfigsCompiler\Twig\Compiler;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class Container
{
    /**
     * @var Object[]
     */
    private $services = array();

    /**
     * @var Object[]
     */
    private $factories = array();

    public function __construct()
    {
        $this->factories = array(
            'prop_parser' => function() {
                return new PropParser();
            },
            'configs_handler' => function() {
                return new ConfigsHandler();
            },
            'compiler' => function() {
                return new Compiler();
            }
        );
    }

    /**
     * @param string $id
     *
     * @return object
     */
    public function get($id)
    {
        if (!isset($this->factories[$id])) {
            throw new \RuntimeException(sprintf('Service with ID "%s" is not declared.', $id));
        }

        if (!isset($this->services[$id])) {
            $this->services[$id] = $this->factories[$id]($this);
        }

        return $this->services[$id];
    }
}