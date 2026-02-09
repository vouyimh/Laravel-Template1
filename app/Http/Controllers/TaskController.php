<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;  // Add this line

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::latest()->get();
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
            'title' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task = Task::create($request->all());
        // Get assignees from request
        $assignees = $request->input('assignees', []);
        if (!empty($assignees)) {
            foreach ($assignees as $userId) {
                DB::table('assigned_users')->insert([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
           $task->load('assignees'); // eager load assigned users
    $users = User::all();
        return view('tasks.edit', compact('task','users'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task->update($request->all());

        $task->assignees()->sync($request->assignees ?? []);
             if ($request->has('assignees')) {
        $assignees = [];
        foreach ($request->assignees as $userId) {
            $assignees[] = [
                'task_id' => $task->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }}
        DB::table('assigned_users')->insert($assignees);

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




