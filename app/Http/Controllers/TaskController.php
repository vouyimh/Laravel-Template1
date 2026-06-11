<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskFile;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->role === 'staff') {
            $tasks = Task::with(['files', 'activities.user', 'assignees'])
                ->whereHas('assignees', fn($q) => $q->where('users.id', $user->id))
                ->latest()
                ->get();
        } else {
            $tasks = Task::with(['files', 'activities.user', 'assignees'])->latest()->get();
        }

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'nullable|string',
            'status'      => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'priority'    => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'due_date'    => 'nullable|date',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'integer|exists:users,id',
        ]);

        $task = Task::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'],
            'priority'    => $data['priority'] ?? 'medium',
            'due_date'    => $data['due_date'] ?? null,
        ]);

        $assignees = $data['assignees'] ?? [];
        if (!empty($assignees)) {
            $task->assignees()->sync($assignees);

            // Notify each newly assigned user (except the person creating)
            $users = User::whereIn('id', $assignees)
                         ->where('id', '!=', Auth::id())
                         ->get();
            foreach ($users as $user) {
                $user->notify(new TaskAssignedNotification($task, Auth::user()));
            }
        }

        $this->logActivity($task->id, 'Created', ['by' => Auth::user()?->name]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['files', 'activities.user', 'assignees']);
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
        $data = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'nullable|string',
            'status'      => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'priority'    => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'due_date'    => 'nullable|date',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'integer|exists:users,id',
        ]);

        $oldStatus      = $task->status;
        $oldAssigneeIds = $task->assignees->pluck('id')->toArray();
        $newAssigneeIds = $data['assignees'] ?? [];

        $task->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'],
            'priority'    => $data['priority'] ?? $task->priority,
            'due_date'    => $data['due_date'] ?? $task->due_date,
        ]);
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
        if ($oldStatus !== $data['status']) {
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
        foreach ($task->files as $file) {
            Storage::disk('public')->delete($file->path);
        }
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /* ============================================================
     |  STAFF WORKFLOW
     | ============================================================
     */

    public function startTask(Request $request, Task $task)
    {
        $this->ensureAssignedStaff($task);

        if ($task->status === 'completed') {
            return back()->with('error', 'Task is already completed.');
        }

        $data = $request->validate([
            'lat'     => 'nullable|numeric',
            'lng'     => 'nullable|numeric',
            'address' => 'nullable|string|max:500',
        ]);

        $location = null;
        if (isset($data['lat'], $data['lng'])) {
            $location = [
                'lat'     => (float) $data['lat'],
                'lng'     => (float) $data['lng'],
                'address' => $data['address'] ?? null,
            ];
        }

        $task->update([
            'status'         => 'in_progress',
            'started_at'     => now(),
            'start_location' => $location,
        ]);

        $this->logActivity($task->id, 'Task started', $location ? ['location' => $location] : null);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'task' => $task->fresh(['files', 'activities'])]);
        }

        return back()->with('success', 'Task started.');
    }

    public function uploadProof(Request $request, Task $task)
    {
        $this->ensureAssignedStaff($task);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:204800', // 200 MB in KB
                'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov,mkv,avi,m4v,ogg,wmv,flv',
            ],
        ]);

        $uploaded = $request->file('file');
        $mime = $uploaded->getMimeType();
        $type = str_starts_with($mime, 'video/') ? 'video' : 'image';

        $path = $uploaded->store("task-proof/{$task->id}", 'public');

        $file = TaskFile::create([
            'task_id'       => $task->id,
            'user_id'       => Auth::id(),
            'type'          => $type,
            'original_name' => $uploaded->getClientOriginalName(),
            'path'          => $path,
            'mime_type'     => $mime,
            'size'          => $uploaded->getSize(),
        ]);

        $this->logActivity($task->id, "Uploaded {$file->original_name}", ['file_id' => $file->id]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'file' => $file->fresh()->append('url')]);
        }

        return back()->with('success', 'Proof uploaded.');
    }

    public function removeProof(Request $request, Task $task, TaskFile $file)
    {
        $this->ensureAssignedStaff($task);

        if ($file->task_id !== $task->id) {
            abort(404);
        }

        Storage::disk('public')->delete($file->path);
        $name = $file->original_name;
        $file->delete();

        $this->logActivity($task->id, "Removed {$name}");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'File removed.');
    }

    public function completeTask(Request $request, Task $task)
    {
        $this->ensureAssignedStaff($task);

        if ($task->files()->count() === 0) {
            $msg = 'At least one proof file is required before completing.';
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'error' => $msg], 422)
                : back()->with('error', $msg);
        }

        $task->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        $this->logActivity($task->id, 'Task completed');

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'task' => $task->fresh(['files', 'activities'])]);
        }

        return back()->with('success', 'Task completed.');
    }

    /* ============================================================
     |  Helpers
     | ============================================================
     */

    protected function ensureAssignedStaff(Task $task): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role !== 'staff' || !$task->isAssignedTo($user->id)) {
            abort(403, 'You are not assigned to this task.');
        }
    }

    protected function logActivity(int $taskId, string $action, ?array $meta = null): void
    {
        TaskActivity::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'action'  => $action,
            'meta'    => $meta,
        ]);
    }
}
