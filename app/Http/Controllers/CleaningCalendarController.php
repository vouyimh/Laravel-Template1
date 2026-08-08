<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CleaningCalendarController extends Controller
{
    public function index()
    {
        $clients = Client::with('houses')->orderBy('company_name')->get();
        $staff   = User::where('role', 'staff')->orderBy('name')->get();

        return view('content.pages.cleaning-calendar', compact('clients', 'staff'));
    }

    public function events(Request $request)
    {
        $data = $request->validate([
            'start'     => 'required|date',
            'end'       => 'required|date|after_or_equal:start',
            'client_id' => 'nullable|integer|exists:clients,client_id',
        ]);

        $tasks = Task::with(['assignees', 'clientHouse.client'])
            ->whereNotNull('client_house_id')
            ->whereDate('due_date', '<=', $data['end'])
            ->where(function ($q) use ($data) {
                $q->whereDate('end_date', '>=', $data['start'])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->whereNull('end_date')->whereDate('due_date', '>=', $data['start']);
                  });
            })
            ->when($data['client_id'] ?? null, function ($q, $clientId) {
                $q->whereHas('clientHouse', fn ($h) => $h->where('client_id', $clientId));
            })
            ->get();

        return response()->json($tasks->map(fn (Task $task) => $this->formatTask($task)));
    }

    public function store(Request $request)
    {
        $data = $this->validateTask($request);

        $task = Task::create([
            'client_house_id' => $data['client_house_id'],
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'status'          => $data['status'],
            'due_date'        => $data['due_date'],
            'end_date'        => $data['end_date'] ?? $data['due_date'],
        ]);

        if (!empty($data['assignee_id'])) {
            $task->assignees()->sync([$data['assignee_id']]);

            $assignee = User::find($data['assignee_id']);
            if ($assignee && $assignee->id !== Auth::id()) {
                $assignee->notify(new TaskAssignedNotification($task, Auth::user()));
            }
        }

        return response()->json($this->formatTask($task->fresh(['assignees', 'clientHouse.client'])), 201);
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($task->client_house_id, 404);

        $data = $this->validateTask($request, $task);

        $oldStatus     = $task->status;
        $oldAssigneeId = $task->assignees->first()?->id;

        $task->update([
            'client_house_id' => $data['client_house_id'],
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'status'          => $data['status'],
            'due_date'        => $data['due_date'],
            'end_date'        => $data['end_date'] ?? $data['due_date'],
        ]);

        $newAssigneeId = $data['assignee_id'] ?? null;
        if ($newAssigneeId !== $oldAssigneeId) {
            $task->assignees()->sync($newAssigneeId ? [$newAssigneeId] : []);

            if ($newAssigneeId) {
                $assignee = User::find($newAssigneeId);
                if ($assignee && $assignee->id !== Auth::id()) {
                    $assignee->notify(new TaskAssignedNotification($task, Auth::user()));
                }
            }
        }

        if ($oldStatus !== $data['status']) {
            $assignee = $task->assignees->first();
            if ($assignee && $assignee->id !== Auth::id()) {
                $assignee->notify(new TaskStatusChangedNotification($task, $oldStatus, Auth::user()));
            }
        }

        return response()->json($this->formatTask($task->fresh(['assignees', 'clientHouse.client'])));
    }

    public function destroy(Task $task)
    {
        abort_unless($task->client_house_id, 404);

        $task->delete();

        return response()->json(['ok' => true]);
    }

    private function validateTask(Request $request, ?Task $task = null): array
    {
        return $request->validate([
            'client_house_id' => 'required|integer|exists:client_houses,id',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'status'          => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'due_date'        => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:due_date',
            'assignee_id'     => 'nullable|integer|exists:users,id',
        ]);
    }

    private function formatTask(Task $task): array
    {
        $house = $task->clientHouse;

        return [
            'id'              => $task->id,
            'client_house_id' => $task->client_house_id,
            'house_address'   => $house?->house_address,
            'client_name'     => $house?->client?->company_name,
            'title'           => $task->title,
            'description'     => $task->description,
            'status'          => $task->status,
            'due_date'        => optional($task->due_date)->format('Y-m-d'),
            'end_date'        => optional($task->end_date ?? $task->due_date)->format('Y-m-d'),
            'assignee_id'     => $task->assignees->first()?->id,
            'assignee_name'   => $task->assignees->first()?->name,
        ];
    }
}
