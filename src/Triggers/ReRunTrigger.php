<?php

namespace the42coders\Workflows\Triggers;

use the42coders\Workflows\Loggers\WorkflowLog;

class ReRunTrigger
{
    /**
     * @param WorkflowLog $log
     * @return void
     */
    public static function startWorkflow(WorkflowLog $log)
    {
        $log->triggerable->start($log->elementable);
    }
}
