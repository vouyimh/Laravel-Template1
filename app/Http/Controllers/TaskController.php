<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('assignees')->latest()->get();
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $task      = Task::create($request->only(['title', 'description', 'status']));
        $assignees = $request->input('assignees', []);
        $task->assignees()->sync($assignees);

        // Notify each newly assigned user (except the person creating)
        if (!empty($assignees)) {
            $users = User::whereIn('id', $assignees)
                         ->where('id', '!=', Auth::id())
                         ->get();
            foreach ($users as $user) {
                $user->notify(new TaskAssignedNotification($task, Auth::user()));
            }
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['assignees', 'comments.user']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $task->load('assignees');
        $users = User::orderBy('name')->get();
        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $oldStatus      = $task->status;
        $oldAssigneeIds = $task->assignees->pluck('id')->toArray();
        $newAssigneeIds = $request->input('assignees', []);

        $task->update($request->only(['title', 'description', 'status']));
        $task->assignees()->sync($newAssigneeIds);

        // Notify newly added assignees
        $addedIds = array_diff($newAssigneeIds, $oldAssigneeIds);
        if (!empty($addedIds)) {
            $newUsers = User::whereIn('id', $addedIds)
                            ->where('id', '!=', Auth::id())
                            ->get();
            foreach ($newUsers as $user) {
                $user->notify(new TaskAssignedNotification($task, Auth::user()));
            }
        }

        // Notify all current assignees if status changed
        if ($oldStatus !== $request->status) {
            $allAssignees = User::whereIn('id', $newAssigneeIds)
                                ->where('id', '!=', Auth::id())
                                ->get();
            foreach ($allAssignees as $user) {
                $user->notify(new TaskStatusChangedNotification($task, $oldStatus, Auth::user()));
            }
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
