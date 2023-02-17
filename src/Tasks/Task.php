<?php

namespace the42coders\Workflows\Tasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use the42coders\Workflows\DataBuses\DataBus;
use the42coders\Workflows\DataBuses\DataBussable;
use the42coders\Workflows\Exceptions\ConditionFailedError;
use the42coders\Workflows\Fields\Fieldable;
use the42coders\Workflows\Loggers\TaskLog;
use the42coders\Workflows\Loggers\WorkflowLog;

class Task extends Model implements TaskInterface
{
    use DataBussable, Fieldable;

    protected $table = 'tasks';

    public string $family = 'task';

    public static string $icon = '<i class="fas fa-question"></i>';

    public ?DataBus $dataBus = null;

    public ?Model $model = null;

    public ?WorkflowLog $workflowLog = null;

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

    public static array $commonFields = [
        'Description' => 'description',
    ];

    protected $casts = [
        'data_fields' => 'array',
    ];

    public static array $fields = [];

    public static array $output = [];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo('the42coders\Workflows\Workflow');
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return MorphTo
     */
    public function parentable(): MorphTo
    {
        return $this->morphTo();
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
    public function newFromBuilder($attributes = [], $connection = null)
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
     * Check if all Conditions for this Action pass.
     *
     * @param Model $model
     * @param DataBus $data
     * @return bool
     * @throws \Exception
     */
    public function checkConditions(Model $model, DataBus $data): bool
    {
        //TODO: This needs to get smoother :(

        if (empty($this->conditions)) {
            return true;
        }

        $conditions = json_decode($this->conditions);

        foreach ($conditions->rules as $rule) {
            $ruleDetails = explode('-', $rule->id);
            [$DataBus, $field] = $ruleDetails;

            $result = config('workflows.data_resources')[$DataBus]::checkCondition($model, $data, $field, $rule->operator, $rule->value);

            if (! $result) {
                throw new \Exception('The Condition for Task '.$this->name.' with the field '.$rule->field.' '.$rule->operator.' '.$rule->value.' failed.');
            }
        }

        return true;
    }

    /**
     * @param Model $model
     * @param DataBus $data
     * @param WorkflowLog $log
     * @return void
     * @throws \Exception
     */
    public function init(Model $model, DataBus $data, WorkflowLog $log)
    {
        $this->model = $model;
        $this->dataBus = $data;
        $this->workflowLog = $log;
        $this->workflowLog->addTaskLog($this->workflowLog->id, $this->id, $this->name, TaskLog::$STATUS_START, json_encode($this->data_fields), \Illuminate\Support\Carbon::now());

        $this->log = TaskLog::createHelper($log->id, $this->id, $this->name);

        $this->dataBus->collectData($model, $this->data_fields);

        $this->checkConditions($this->model, $this->dataBus);
    }

    /**
     * Execute the Task return Value tells you about the success.
     *
     * @return void
     */
    public function execute(): void
    {
    }

    /**
     * @return string|bool
     * @throws \Throwable
     */
    public function pastExecute(): string|bool
    {
        if (empty($this->children)) {
            return 'nothing to do'; //TODO: TASK IS FINISHED
        }
        $this->log->finish();
        $this->workflowLog->updateTaskLog($this->id, '', TaskLog::$STATUS_FINISHED, \Illuminate\Support\Carbon::now());
        foreach ($this->children as $child) {
            $child->init($this->model, $this->dataBus, $this->workflowLog);
            try {
                $child->execute();
            } catch (\Throwable $e) {
                $child->workflowLog->updateTaskLog($child->id, $e->getMessage(), TaskLog::$STATUS_ERROR, \Illuminate\Support\Carbon::now());
                throw $e;
            }
            $child->pastExecute();
        }

        return true;
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
