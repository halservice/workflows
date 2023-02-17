<?php

namespace the42coders\Workflows\Loggers;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use the42coders\Workflows\DataBuses\DataBus;
use the42coders\Workflows\Triggers\Trigger;
use the42coders\Workflows\Concerns\WorkflowObservable;

class WorkflowLog extends Model
{
    use WorkflowObservable;

    protected $table = 'workflow_logs';

    public static $STATUS_START = 'start';

    public static $STATUS_FINISHED = 'finished';

    public static $STATUS_ERROR = 'error';

    private $taskLogsArray = [];

    protected $dates = [
        'start',
        'end',
    ];

    protected $fillable = [
        'workflow_id',
        'name',
        'status',
        'message',
        'start',
        'elementable_id',
        'elementable_type',
        'triggerable_id',
        'triggerable_type',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo('the42coders\Workflows\Workflow');
    }

    /**
     * @return HasMany
     */
    public function taskLogs(): HasMany
    {
        return $this->hasMany('the42coders\Workflows\Loggers\TaskLog');
    }

    /**
     * @return MorphTo
     */
    public function elementable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function triggerable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param Model $workflow
     * @param Model $element
     * @param Trigger $trigger
     * @return WorkflowLog
     */
    public static function createHelper(Model $workflow, Model $element, Trigger $trigger): WorkflowLog
    {
        return WorkflowLog::create([
            'workflow_id' => $workflow->id,
            'name' => $workflow->name,
            'elementable_id' => $element->id,
            'elementable_type' => get_class($element),
            'triggerable_id' => $trigger->id,
            'triggerable_type' => get_class($trigger),
            'status' => self::$STATUS_START,
            'message' => '',
            'start' => now(),
        ]);
    }

    /**
     * @param string $errorMessage
     * @param DataBus $dataBus
     * @return void
     */
    public function setError(string $errorMessage, DataBus $dataBus)
    {
        $this->message = $errorMessage;
        //$this->databus = $dataBus->toString();
        $this->status = self::$STATUS_ERROR;
        $this->end = Carbon::now();
        $this->save();
    }

    /**
     * @return void
     */
    public function finish()
    {
        $this->status = self::$STATUS_FINISHED;
        $this->end = now();
        $this->save();
    }

    /**
     * @param int $workflowLogId
     * @param int $taskId
     * @param string $taskName
     * @param string $status
     * @param string $message
     * @param DateTime $start
     * @param DateTime|null $end
     * @return void
     */
    public function addTaskLog(int $workflowLogId, int $taskId, string $taskName, string $status, string $message, DateTime $start, DateTime $end = null)
    {
        $this->taskLogsArray[$taskId] = [
            'workflow_log_id' => $workflowLogId,
            'task_id' => $taskId,
            'task_name' => $taskName,
            'status' => $status,
            'message' => $message,
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * @param int $task_id
     * @param string $message
     * @param string $status
     * @param DateTime $end
     * @return void
     */
    public function updateTaskLog(int $task_id, string $message, string $status, DateTime $end)
    {
        $this->taskLogsArray[$task_id]['message'] = $message;
        $this->taskLogsArray[$task_id]['status'] = $status;
        $this->taskLogsArray[$task_id]['end'] = $end;
    }

    /**
     * @return void
     */
    public function createTaskLogsFromMemory()
    {
        foreach ($this->taskLogsArray as $taskLog) {
            TaskLog::updateOrCreate(
                [
                    'workflow_log_id' => $taskLog['workflow_log_id'],
                    'task_id' => $taskLog['task_id'],
                ],
                [
                    'name' => $taskLog['task_name'],
                    'status' => $taskLog['status'],
                    'message' => $taskLog['message'],
                    'start' => $taskLog['start'],
                    'end' => $taskLog['end'],
                ]
            );
        }
    }
}
