<?php

namespace App;

use Exception;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{

    /**
     * @inheritDoc
     */
    public function registerBundles(): iterable
    {
        return [];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/../config/services.yml');
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this->createCollectingCompilerPass());
    }

    private function createCollectingCompilerPass(): CompilerPassInterface
    {
        return new class implements CompilerPassInterface
        {
            public function process(ContainerBuilder $container)
            {
                $applicationDefinition = $container->findDefinition(Application::class);

                foreach ( $container->getDefinitions() as $definition) {
                    if (! is_a($definition->getClass(), Command::class, true)) {
                        continue;
                    }
                    $applicationDefinition->addMethodCall('add', [new Reference($definition->getClass())]);
                }
            }
        };
    }
}