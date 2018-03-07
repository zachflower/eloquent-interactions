<?php

namespace ZachFlower\EloquentInteractions\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MakeInteractionCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:interaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new interaction';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Interaction';

    /**
     * Build the class with the given name.
     *
     * Remove the base interaction import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $interactionNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["use {$interactionNamespace}\Interaction;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/interaction.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Interactions';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the interaction.'],
        ];
    }
}
