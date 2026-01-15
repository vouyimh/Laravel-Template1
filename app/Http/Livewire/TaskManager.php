<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;
use Livewire\WithPagination;

class TaskManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $taskId;

    // Form properties
    public $title = '';
    public $description = '';
    public $status = 'pending';
    public $due_date = '';

    // Search and filter
    public $search = '';
    public $statusFilter = '';

    protected $rules = [
        'title' => 'required|min:3|max:255',
        'description' => 'nullable|max:1000',
        'status' => 'required|in:pending,in_progress,completed',
        'due_date' => 'nullable|date|after_or_equal:today',
    ];

    public function render()
    {
        $tasks = Task::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.task-manager', compact('tasks'));
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editMode = false;
        $this->taskId = null;
        $this->title = '';
        $this->description = '';
        $this->status = 'pending';
        $this->due_date = '';
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date ?: null,
        ]);

        $this->closeModal();
        session()->flash('message', 'Task created successfully!');
    }

    public function edit($taskId)
    {
        $task = Task::findOrFail($taskId);

        $this->taskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->status = $task->status;
        $this->due_date = $task->due_date ? $task->due_date->format('Y-m-d') : '';

        $this->editMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        $task = Task::findOrFail($this->taskId);
        $task->update([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date ?: null,
        ]);

        $this->closeModal();
        session()->flash('message', 'Task updated successfully!');
    }

    public function delete($taskId)
    {
        Task::findOrFail($taskId)->delete();
        session()->flash('message', 'Task deleted successfully!');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
}