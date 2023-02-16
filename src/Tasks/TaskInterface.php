<?php

namespace the42coders\Workflows\Tasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use the42coders\Workflows\DataBuses\DataBus;

interface TaskInterface
{
    /**
     * Execute the Action return Value tells you about the success.
     *
     * @param  Model  $model
     * @param  Collection  $data
     * @return Collection
     */
    public function execute(): void;

    /**
     * Checks if all Conditions pass for this Action.
     */
    public function checkConditions(Model $model, DataBus $data): bool;
}
