<?php

namespace the42coders\Workflows\Loggers;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $table = 'task_logs';

    public static $STATUS_START = 'start';

    public static $STATUS_FINISHED = 'finished';

    public static $STATUS_ERROR = 'error';

    protected $fillable = [
        'status',
        'workflow_log_id',
        'task_id',
        'name',
        'message',
        'start',
        'end',
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
     * @param int $workflowLogId
     * @param int $taskId
     * @param string $taskName
     * @param string|null $status
     * @param string $message
     * @param $start
     * @param $end
     * @return TaskLog
     */
    public static function createHelper(int $workflowLogId, int $taskId, string $taskName, string $status = null, string $message = '', $start = null, $end = null): TaskLog
    {
        return TaskLog::create([
            'status' => $status ?? self::$STATUS_START,
            'workflow_log_id' => $workflowLogId,
            'task_id' => $taskId,
            'name' => $taskName,
            'message' => $message,
            'start' => $start ?? now(),
            'end' => $end,
        ]);
    }

    /**
     * @param string $errorMessage
     * @return void
     */
    public function setError(string $errorMessage)
    {
        $this->message = $errorMessage;
        $this->status = self::$STATUS_ERROR;
        $this->end = now();
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
}
