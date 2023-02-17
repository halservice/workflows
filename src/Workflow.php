<?php

namespace the42coders\Workflows;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    private array $data;

    protected $table = 'workflows';

    protected $fillable = [
        'name',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany('the42coders\Workflows\Tasks\Task');
    }

    /**
     * @return HasMany
     */
    public function triggers(): HasMany
    {
        return $this->hasMany('the42coders\Workflows\Triggers\Trigger');
    }

    /**
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany('the42coders\Workflows\Loggers\WorkflowLog');
    }

    /**
     * @param string $class
     * @return Model|HasMany|object|null
     */
    public function getTriggerByClass(string $class)
    {
        return $this->triggers()->where('type', $class)->first();
    }
}
