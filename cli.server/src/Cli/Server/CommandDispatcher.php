<?php

namespace Symsonte\Cli\Server;

use Symsonte\Cli\Server;
use Symsonte\Resource\Builder;
use Symsonte\Service\CachedInstantiator;
use Symsonte\Service\ConstructorInstantiator;
use Symsonte\Service\Container;
use Symsonte\Service\Declaration;
use Symsonte\Service\DeductibleContainer;
use Symsonte\Service\OrdinaryContainer;
use Symsonte\ServiceKit\Resource\Loader;
use Symsonte\Service\Declaration\Argument\ServiceProcessor as ServiceArgumentProcessor;
use Symsonte\Service\Declaration\Call\Processor as CallProcessor;
use Symsonte\ConstructorInstantiator as BaseConstructorInstantiator;
use Symsonte\Cli\Server\Input\Resolution\Finder;
use Symsonte\ServiceKit\Declaration\Bag;
use Symsonte\Service\Declaration\Storer;
use Symsonte\Service\Declaration\Call;
use Symsonte\Service\Declaration\IdStorer;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 */
class CommandDispatcher
{
    /**
     * @var Loader
     */
    private $resourceLoader;

    /**
     * @var Container
     */
    private $serviceContainer;

    /**
     * @var Finder
     */
    private $commandFinder;

    /**
     * @var Server
     */
    private $server;

    /**
     * @param Loader              $resourceLoader
     * @param Container           $serviceContainer
     * @param Finder              $commandFinder
     * @param Server              $server
     */
    function __construct(
        Loader $resourceLoader,
        Container $serviceContainer,
        Finder $commandFinder,
        Server $server
    )
    {
        $this->resourceLoader = $resourceLoader;
        $this->serviceContainer = $serviceContainer;
        $this->commandFinder = $commandFinder;
        $this->server = $server;
    }

    /**
     */
    public function dispatch()
    {
        $input = $this->server->resolveInput();

        $command = $this->commandFinder->first($input);
        $command = $this->createContainer()->get($command);

        call_user_func_array([$command, '__invoke'], []);
    }

    /**
     * @return DeductibleContainer
     */
    private function createContainer()
    {
        $bag = $this->resourceLoader->load([
            'dir' => sprintf("%s/../../../../../../../cli", __DIR__),
            'filter' => '*.php',
            'extra' => [
                'type' => 'annotation',
                'annotation' => '/^di\\\\command/'
            ]
        ]);

        $declarationStorer = $this->createDeclarationStorer($bag);
        $argumentProcessor = new ServiceArgumentProcessor();
        $argumentProcessor->setContainer($this->serviceContainer);

        return new DeductibleContainer(
            $this->createDeductibleStorer($bag),
            $declarationStorer,
            new OrdinaryContainer(
                $declarationStorer,
                new CachedInstantiator(
                    new ConstructorInstantiator(
                        $argumentProcessor,
                        new CallProcessor($argumentProcessor),
                        new BaseConstructorInstantiator()
                    )
                )
            )
        );
    }

    /**
     * @param Bag $bag
     *
     * @return IdStorer
     */
    private function createDeductibleStorer(Bag $bag)
    {
        $storer = new IdStorer();

        foreach ($bag->getDeclarations() as $declaration) {
            if ($declaration->isDeductible()) {
                $storer->add($declaration->getDeclaration()->getId());
            }
        }

        return $storer;
    }

    /**
     * @param Bag $bag
     *
     * @return Storer
     */
    private function createDeclarationStorer(Bag $bag)
    {
        $storer = new Storer();

        foreach ($bag->getDeclarations() as $declaration) {
            $storer->add($declaration->getDeclaration());
        }

        return $storer;
    }
}