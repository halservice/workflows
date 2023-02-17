<?php

namespace the42coders\Workflows\Triggers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use the42coders\Workflows\DataBuses\DataBus;
use the42coders\Workflows\DataBuses\DataBussable;
use the42coders\Workflows\Fields\Fieldable;
use the42coders\Workflows\Jobs\ProcessWorkflow;
use the42coders\Workflows\Loggers\WorkflowLog;

class Trigger extends Model
{
    use DataBussable, Fieldable;

    protected $table = 'triggers';

    public string $family = 'trigger';

    public static string $icon = '<i class="fas fa-question"></i>';

    protected $fillable = [
        'workflow_id',
        'parent_id',
        'type',
        'name',
        'data',
        'node_id',
        'pos_x',
        'pos_y',
    ];

    public static array $output = [];

    public static array $fields = [];

    public static array $fields_definitions = [];

    protected $casts = [
        'data_fields' => 'array',
    ];

    public static array $commonFields = [
        'Description' => 'description',
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
     * @return MorphMany
     */
    public function children(): MorphMany
    {
        return $this->morphMany('the42coders\Workflows\Tasks\Task', 'parentable');
    }

    /**
     * Return Collection of models by type.
     *
     * @param  array  $attributes
     * @param  null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null): static
    {
        $entryClassName = '\\'.Arr::get((array) $attributes, 'type');

        if (class_exists($entryClassName)
            && is_subclass_of($entryClassName, self::class)
        ) {
            $model = new $entryClassName();
        } else {
            $model = $this->newInstance();
        }

        $model->exists = true;
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->connection);

        return $model;
    }

    /**
     * @param Model $model
     * @param array $data
     * @return void
     */
    public function start(Model $model, array $data = []): void
    {
        $log = WorkflowLog::createHelper($this->workflow, $model, $this);
        $dataBus = new DataBus($data);

        ProcessWorkflow::dispatch($model, $dataBus, $this, $log);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function getSettings()
    {
        return view('workflows::layouts.settings_overlay', [
            'element' => $this,
        ]);
    }

    /**
     * @return string
     */
    public static function getTranslation(): string
    {
        return __(static::getTranslationKey());
    }

    /**
     * @return string
     */
    public static function getTranslationKey(): string
    {
        $className = (new \ReflectionClass(new static))->getShortName();

        return "workflows::workflows.Elements.{$className}";
    }
}
