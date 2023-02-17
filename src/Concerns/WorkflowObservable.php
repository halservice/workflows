<?php

namespace the42coders\Workflows\Concerns;

use Illuminate\Database\Eloquent\Model;
use the42coders\Workflows\Triggers\Trigger;

trait WorkflowObservable
{
    /**
     * @return void
     */
    public static function bootWorkflowObservable(): void
    {
        static::retrieved(static function (Model $model) {
            self::startWorkflows($model, 'retrieved');
        });
        static::creating(static function (Model $model) {
            self::startWorkflows($model, 'creating');
        });
        static::created(static function (Model $model) {
            self::startWorkflows($model, 'created');
        });
        static::updating(static function (Model $model) {
            self::startWorkflows($model, 'updating');
        });
        static::updated(static function (Model $model) {
            self::startWorkflows($model, 'updated');
        });
        static::saving(static function (Model $model) {
            self::startWorkflows($model, 'saving');
        });
        static::saved(static function (Model $model) {
            self::startWorkflows($model, 'saved');
        });
        static::deleting(static function (Model $model) {
            self::startWorkflows($model, 'deleting');
        });
        static::deleted(static function (Model $model) {
            self::startWorkflows($model, 'deleted');
        });
        //TODO: check why they are not available here
        /*static::restoring(function (Model $model) {
           self::startWorkflows($model, 'restoring');
        });
        static::restored(function (Model $model) {
           self::startWorkflows($model, 'restored');
        });
        static::forceDeleted(function (Model $model) {
            self::startWorkflows($model, 'forceDeleted');
        });*/
    }

    /**
     * @param string $class
     * @param string $event
     * @return mixed
     */
    public static function getRegisteredTriggers(string $class, string $event)
    {
        $classArray = explode('\\', $class);

        $className = $classArray[count($classArray) - 1];

        return Trigger::where('type', 'the42coders\Workflows\Triggers\ObserverTrigger')
            ->where('data_fields->class->value', 'like', '%'.$className.'%')
            ->where('data_fields->event->value', $event)
            ->get();
    }

    /**
     * @param Model $model
     * @param string $event
     * @return false|void
     */
    public static function startWorkflows(Model $model, string $event)
    {
        if (! in_array($event, config('workflows.triggers.Observers.events'))) {
            return false;
        }

        foreach (self::getRegisteredTriggers(get_class($model), $event) as $trigger) {
            $trigger->start($model);
        }
    }
}
