<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Task Manager</h1>
        <flux:button wire:click="openModal" variant="primary">
            Add New Task
        </flux:button>
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <flux:alert class="mb-6">
            {{ session('message') }}
        </flux:alert>
    @endif

    <!-- Search and Filter -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <flux:input wire:model.live="search" placeholder="Search tasks..." icon="magnifying-glass" />

        <flux:select wire:model.live="statusFilter" placeholder="Filter by status">
            <flux:option value="">All Status</flux:option>
            <flux:option value="pending">Pending</flux:option>
            <flux:option value="in_progress">In Progress</flux:option>
            <flux:option value="completed">Completed</flux:option>
        </flux:select>
    </div>

    <!-- Tasks Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <flux:table>
            <flux:columns>
                <flux:column>Title</flux:column>
                <flux:column>Description</flux:column>
                <flux:column>Status</flux:column>
                <flux:column>Due Date</flux:column>
                <flux:column>Actions</flux:column>
            </flux:columns>

            <flux:rows>
                @forelse($tasks as $task)
                    <flux:row>
                        <flux:cell class="font-medium">{{ $task->title }}</flux:cell>
                        <flux:cell>
                            {{ Str::limit($task->description, 50) }}
                        </flux:cell>
                        <flux:cell>
                            <flux:badge :color="$task->status_color">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </flux:badge>
                        </flux:cell>
                        <flux:cell>
                            {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}
                        </flux:cell>
                        <flux:cell>
                            <div class="flex space-x-2">
                                <flux:button wire:click="edit({{ $task->id }})" size="sm" variant="ghost">
                                    Edit
                                </flux:button>
                                <flux:button wire:click="delete({{ $task->id }})"
                                    wire:confirm="Are you sure you want to delete this task?" size="sm" variant="danger">
                                    Delete
                                </flux:button>
                            </div>
                        </flux:cell>
                    </flux:row>
                @empty
                    <flux:row>
                        <flux:cell colspan="5" class="text-center py-8 text-gray-500">
                            No tasks found
                        </flux:cell>
                    </flux:row>
                @endforelse
            </flux:rows>
        </flux:table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $tasks->links() }}
        </div>
    </div>

    <!-- Modal -->
    <flux:modal wire:model="showModal">
        <form wire:submit="{{ $editMode ? 'update' : 'store' }}">
            <div class="space-y-4">
                <flux:heading size="lg">
                    {{ $editMode ? 'Edit Task' : 'Create New Task' }}
                </flux:heading>

                <flux:input wire:model="title" label="Title" placeholder="Enter task title" required />
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <flux:textarea wire:model="description" label="Description"
                    placeholder="Enter task description (optional)" rows="3" />
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <flux:select wire:model="status" label="Status">
                    <flux:option value="pending">Pending</flux:option>
                    <flux:option value="in_progress">In Progress</flux:option>
                    <flux:option value="completed">Completed</flux:option>
                </flux:select>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <flux:input wire:model="due_date" label="Due Date" type="date" />
                @error('due_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <flux:button type="button" wire:click="closeModal" variant="ghost">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editMode ? 'Update Task' : 'Create Task' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>