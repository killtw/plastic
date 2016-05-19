<?php

namespace Sleimanx2\Plastic\Map;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Sleimanx2\Plastic\Connection;
use Sleimanx2\Plastic\Exception\InvalidArgumentException;

class Map
{
    /**
     * Plastic connection instance
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Map grammar instance
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * Blueprint resolver callback
     *
     * @var Closure
     */
    protected $resolver;

    /**
     * Schema constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @param Model $model
     * @param Closure $callback
     */
    public function create(Model $model, Closure $callback)
    {
        $traits = class_uses($model);

        if (!isset($traits['Searchable'])) {
            throw new InvalidArgumentException(get_class($model) . ' does not use the searchable trait');
        }

        $blueprint = $this->createBlueprint($model->getType());

        $blueprint->create();

        $callback($blueprint);

        $this->build($blueprint);
    }

    /**
     * Execute the blueprint to build.
     *
     * @param  Blueprint $blueprint
     */
    protected function build(Blueprint $blueprint)
    {
        $blueprint->build($this->connection, $this->grammar);
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param  string $table
     * @param  Closure|null $callback
     * @return mixed|Blueprint
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback);
        }

        return new Blueprint($table, $callback);
    }

    /**
     * Set the Schema Blueprint resolver callback.
     *
     * @param  \Closure $resolver
     * @return void
     */
    public function blueprintResolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }

}