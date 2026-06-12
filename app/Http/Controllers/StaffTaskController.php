<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffTaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Task::with(['files', 'activities.user', 'assignees']);

        if ($user && $user->role === 'staff') {
            $query->whereHas('assignees', fn($q) => $q->where('users.id', $user->id));
        }

        $tasks = $query->latest()->get();

        return view('stafftask.board', compact('tasks'));
    }

    public function create()
    {
        $users = User::where('role', 'staff')->orderBy('name')->get();
        return view('stafftask.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'nullable|string',
            'priority'    => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'due_date'    => 'nullable|date',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'integer|exists:users,id',
        ]);

        $task = Task::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'status'      => 'pending',
            'priority'    => $data['priority'] ?? 'medium',
            'due_date'    => $data['due_date'] ?? null,
        ]);

        if (!empty($data['assignees'])) {
            $task->assignees()->sync($data['assignees']);
        }

        $this->logActivity($task->id, 'Created on StaffTask Board');

        return redirect()
            ->route('stafftask.board')
            ->with('success', 'Task created on StaffTask Board.');
    }

    public function show(Task $task)
    {
        $task->load(['files', 'activities.user', 'assignees']);
        return view('stafftask.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->ensureAdmin();
        $task->load(['assignees', 'files']);
        $users = User::where('role', 'staff')->orderBy('name')->get();
        return view('stafftask.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'nullable|string',
            'status'      => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'priority'    => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'due_date'    => 'nullable|date',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'integer|exists:users,id',
        ]);

        $task->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'],
            'priority'    => $data['priority'] ?? $task->priority,
            'due_date'    => $data['due_date'] ?? $task->due_date,
        ]);

        $task->assignees()->sync($data['assignees'] ?? []);

        $this->logActivity($task->id, 'Updated on StaffTask Board');

        return redirect()
            ->route('stafftask.board')
            ->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $this->ensureAdmin();

        foreach ($task->files as $file) {
            Storage::disk('uploads')->delete($file->path);
        }
        $task->delete();

        return redirect()
            ->route('stafftask.board')
            ->with('success', 'Task deleted.');
    }

    /* ============================================================
     | DRAG & DROP — move between Kanban columns
     | ============================================================ */
    public function move(Request $request, Task $task)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
        ]);

        $update = ['status' => $data['status']];

        if ($data['status'] === 'in_progress' && !$task->started_at) {
            $update['started_at'] = now();
        }
        if ($data['status'] === 'completed' && !$task->completed_at) {
            $update['completed_at'] = now();
        }
        if ($data['status'] === 'pending') {
            $update['started_at']   = null;
            $update['completed_at'] = null;
        }

        $task->update($update);
        $this->logActivity($task->id, 'Moved to ' . $data['status']);

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'status' => $task->status])
            : back()->with('success', 'Task moved.');
    }

    /* ============================================================
     | STAFF WORKFLOW (mirrors TaskController but isolated)
     | ============================================================ */
    public function startTask(Request $request, Task $task)
    {
        $this->ensureAssignedStaff($task);

        if ($task->status === 'completed') {
            return $this->respond($request, false, 'Task is already completed.', 422);
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

        return $this->respond($request, true, 'Task started.');
    }

    public function uploadProof(Request $request, Task $task)
    {
        $this->ensureAssignedStaff($task);

        $request->validate([
            'file' => [
                'required', 'file', 'max:204800',
                'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov,mkv,avi,m4v,ogg,wmv,flv',
            ],
        ]);

        $uploaded = $request->file('file');
        $mime = $uploaded->getMimeType();
        $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
        $path = $uploaded->store("task-proof/{$task->id}", 'uploads');

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

        return $this->respond($request, true, 'Proof uploaded.');
    }

    public function serveFile(Request $request, Task $task, TaskFile $file)
    {
        // Only users who can see the task may stream its files.
        $user = Auth::user();
        if (!$user) abort(401);
        if ($user->role === 'staff' && !$task->isAssignedTo($user->id)) {
            abort(403, 'You are not assigned to this task.');
        }
        if ($file->task_id !== $task->id) abort(404);

        $disk = Storage::disk('uploads');
        if (!$disk->exists($file->path)) abort(404, 'File missing on disk.');

        $absolutePath = $disk->path($file->path);
        $name         = $file->original_name ?: basename($file->path);

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $name, [
                'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            ]);
        }

        // Inline streaming so <img>/<video> and the preview modal can load it.
        return response()->file($absolutePath, [
            'Content-Type'        => $file->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($name) . '"',
        ]);
    }

    public function removeProof(Request $request, Task $task, TaskFile $file)
    {
        $this->ensureAssignedStaff($task);

        if ((int) $file->task_id !== (int) $task->id) {
            abort(404);
        }

        Storage::disk('uploads')->delete($file->path);
        $name = $file->original_name;
        $file->delete();

        $this->logActivity($task->id, "Removed {$name}");

        return $this->respond($request, true, 'File removed.');
    }

    public function completeTask(Request $request, Task $task)
    {
        $this->ensureAssignedStaff($task);

        if ($task->files()->count() === 0) {
            return $this->respond($request, false, 'Upload at least one proof file before completing.', 422);
        }

        $task->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        $this->logActivity($task->id, 'Task completed');

        return $this->respond($request, true, 'Task completed.');
    }

    /* ============================================================
     | Helpers
     | ============================================================ */
    protected function ensureAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can perform this action.');
        }
    }

    protected function ensureAssignedStaff(Task $task): void
    {
        $user = Auth::user();
        if (!$user) abort(401);
        if ($user->role === 'admin') return;
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

    protected function respond(Request $request, bool $ok, string $message, int $statusCode = 200)
    {
        if ($request->wantsJson()) {
            return response()->json(
                ['ok' => $ok, 'message' => $message],
                $ok ? $statusCode : ($statusCode === 200 ? 400 : $statusCode)
            );
        }
        return back()->with($ok ? 'success' : 'error', $message);
    }
}
