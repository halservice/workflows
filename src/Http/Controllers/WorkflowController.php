<?php

namespace the42coders\Workflows\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use the42coders\Workflows\Loggers\WorkflowLog;
use the42coders\Workflows\Tasks\Task;
use the42coders\Workflows\Triggers\ReRunTrigger;
use the42coders\Workflows\Triggers\Trigger;
//use App\Http\Controllers\Controller;
use the42coders\Workflows\Workflow;

class WorkflowController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $workflows = Workflow::paginate(25);

        return view('workflows::index', ['workflows' => $workflows]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(int $id)
    {
        $workflow = Workflow::find($id);

        return view('workflows::diagram', ['workflow' => $workflow]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('workflows::create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $workflow = Workflow::create($request->all());

        return redirect(route('workflow.show', ['workflow' => $workflow]));
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(int $id)
    {
        $workflow = Workflow::find($id);

        return view('workflows::edit', [
            'workflow' => $workflow,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id)
    {
        $workflow = Workflow::find($id);

        $workflow->update($request->all());

        return redirect(route('workflow.index'));
    }

    /**
     * @param int $id
     *
     * Deletes the Workflow and over cascading also the Tasks, TaskLogs, WorkflowLogs and Triggers.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(int $id)
    {
        $workflow = Workflow::find($id);

        $workflow->delete();

        return redirect(route('workflow.index'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return array|string[]
     */
    public function addTask(int $id, Request $request)
    {
        $workflow = Workflow::find($id);
        if ($request->data['type'] === 'trigger') {
            return [
                'task' => '',
            ];
        }
        $task = Task::where('workflow_id', $workflow->id)->where('node_id', $request->id)->first();

        if (! empty($task)) {
            $task->pos_x = $request->pos_x;
            $task->pos_y = $request->pos_y;
            $task->save();

            return ['task' => $task];
        }

        if (array_key_exists($request->name, config('workflows.tasks'))) {
            $task = config('workflows.tasks')[$request->name]::create([
                'type' => config('workflows.tasks')[$request->name],
                'workflow_id' => $workflow->id,
                'name' => $request->name,
                'data_fields' => null,
                'node_id' => $request->id,
                'pos_x' => $request->pos_x,
                'pos_y' => $request->pos_y,
            ]);
        }

        return [
            'task' => $task,
            'node_id' => $request->id,
        ];
    }

    /**
     * @param int $id
     * @param Request $request
     * @return array
     */
    public function addTrigger(int $id, Request $request)
    {
        $workflow = Workflow::find($id);

        if (array_key_exists($request->name, config('workflows.triggers.types'))) {
            $trigger = config('workflows.triggers.types')[$request->name]::create([
                'type' => config('workflows.triggers.types')[$request->name],
                'workflow_id' => $workflow->id,
                'name' => $request->name,
                'data_fields' => null,
                'pos_x' => $request->pos_x,
                'pos_y' => $request->pos_y,
            ]);
        }

        return [
            'trigger' => $trigger,
            'node_id' => $request->id,
        ];
    }

    /**
     * @param int $id
     * @param Request $request
     * @return mixed
     */
    public function changeConditions(int $id, Request $request)
    {
        $workflow = Workflow::find($id);

        if ($request->type === 'task') {
            $element = $workflow->tasks->find($request->id);
        }

        if ($request->type === 'trigger') {
            $element = $workflow->triggers->find($request->id);
        }

        $element->conditions = $request->data;
        $element->save();

        return $element;
    }

    /**
     * @param int $id
     * @param Request $request
     * @return mixed
     */
    public function changeValues(int $id, Request $request)
    {
        $workflow = Workflow::find($id);

        if ($request->type === 'task') {
            $element = $workflow->tasks->find($request->id);
        }

        if ($request->type === 'trigger') {
            $element = $workflow->triggers->find($request->id);
        }

        $data = [];

        foreach ($request->data as $key => $value) {
            $path = explode('->', $key);
            $data[$path[0]][$path[1]] = $value;
        }
        $element->data_fields = $data;
        $element->save();

        return $element;
    }

    /**
     * @param int $id
     * @param Request $request
     * @return string[]
     */
    public function updateNodePosition(int $id, Request $request)
    {
        $element = $this->getElementByNode($id, $request->node);

        $element->pos_x = $request->input('node.pos_x');
        $element->pos_y = $request->input('node.pos_y');
        $element->save();

        return ['status' => 'success'];
    }

    /**
     * @param int $workflowId
     * @param array $node
     * @return Model
     */
    public function getElementByNode(int $workflowId, array $node): Model
    {
        return match ($node['data']['type']) {
            'task' => Task::where('workflow_id', $workflowId)->where('id', $node['data']['task_id'])->first(),
            'trigger' => Trigger::where('workflow_id', $workflowId)->where('id', $node['data']['trigger_id'])->first(),
            default => null
        };
    }

    /**
     * @param int $id
     * @param Request $request
     * @return string[]
     */
    public function addConnection(int $id, Request $request)
    {
        $workflow = Workflow::find($id);

        $parentElement = match ($request->parent_element['data']['type']) {
            'trigger' => Trigger::where('workflow_id', $workflow->id)->where('id', $request->parent_element['data']['trigger_id'])->first(),
            'task' => Task::where('workflow_id', $workflow->id)->where('id', $request->parent_element['data']['task_id'])->first(),
        };

        $childElement = match ($request->child_element['data']['type']) {
            'trigger' => Trigger::where('workflow_id', $workflow->id)->where('id', $request->child_element['data']['trigger_id'])->first(),
            'task' => Task::where('workflow_id', $workflow->id)->where('id', $request->child_element['data']['task_id'])->first(),
        };

        $childElement->parentable_id = $parentElement->id;
        $childElement->parentable_type = get_class($parentElement);

        $childElement->save();

        return ['status' => 'success'];
    }

    /**
     * @param int $id
     * @param Request $request
     * @return string[]
     */
    public function removeConnection(int $id, Request $request)
    {
        $workflow = Workflow::find($id);

        $childTask = Task::where('workflow_id', $workflow->id)->where('node_id', $request->input_id)->first();

        $childTask->parentable_id = 0;
        $childTask->parentable_type = null;
        $childTask->save();

        return ['status' => 'success'];
    }

    /**
     * @param int $id
     * @param Request $request
     * @return string[]
     */
    public function removeTask(int $id, Request $request)
    {
//        $workflow = Workflow::find($id);

        $element = $this->getElementByNode($id, $request->node);

        $element->delete();

        return [
            'status' => 'success',
        ];
    }

    /**
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function getElementSettings(int $id, Request $request)
    {
        $workflow = Workflow::find($id);

        $element = match ($request->type) {
            'task' => Task::where('workflow_id', $workflow->id)->where('id', $request->element_id)->first(),
            'trigger' => Trigger::where('workflow_id', $workflow->id)->where('id', $request->element_id)->first(),
        };

        return view('workflows::layouts.settings_overlay', [
            'element' => $element,
        ]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function getElementConditions(int $id, Request $request)
    {
        $workflow = Workflow::find($id);

        $element = match ($request->type) {
            'task' => Task::where('workflow_id', $workflow->id)->where('id', $request->element_id)->first(),
            'trigger' => Trigger::where('workflow_id', $workflow->id)->where('id', $request->element_id)->first(),
        };

        $filter = [];

        foreach (config('workflows.data_resources') as $resourceName => $resourceClass) {
            $filter[$resourceName] = $resourceClass::getValues($element, null, null);
        }

        return view('workflows::layouts.conditions_overlay', [
            'element' => $element,
            'conditions' => $element->conditions,
            'allFilters' => $filter,
        ]);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadResourceIntelligence(int $id, Request $request): \Illuminate\Http\JsonResponse
    {
        $workflow = Workflow::find($id);
        $html = '';

        $element = match ($request->type) {
            'task' => Task::where('workflow_id', $workflow->id)->where('id', $request->element_id)->first(),
            'trigger' => Trigger::where('workflow_id', $workflow->id)->where('id', $request->element_id)->first(),
        };

        if (in_array($request->resource, config('workflows.data_resources'))) {
            $className = $request->resource ?? 'the42coders\\Workflows\\DataBuses\\ValueResource';
            $resource = new $className();
            $html = $resource::loadResourceIntelligence($element, $request->value, $request->field_name);
        }

        return response()->json([
            'html' => $html,
            'id' => $request->field_name,
        ]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function getLogs(int $id)
    {
        $workflow = Workflow::find($id);

        $workflowLogs = $workflow->logs()->orderBy('start', 'desc')->get();
        //TODO: get Pagination working

        return view('workflows::layouts.logs_overlay', [
            'workflowLogs' => $workflowLogs,
        ]);
    }

    /**
     * @param int $workflowLogId
     * @return string[]
     */
    public function reRun(int $workflowLogId)
    {
        $log = WorkflowLog::find($workflowLogId);

        ReRunTrigger::startWorkflow($log);

        return [
            'status' => 'started',
        ];
    }

    /**
     * @param Request $request
     * @param int $triggerId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function triggerButton(Request $request, int $triggerId)
    {
        $trigger = Trigger::findOrFail($triggerId);
        $className = $request->model_class;
        $resource = new $className();

        $model = $resource->find($request->model_id);

        $trigger->start($model, []);

        return redirect()->back()->with('sucess', 'Button Triggered a Workflow');
    }
}
