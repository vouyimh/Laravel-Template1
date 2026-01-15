<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - Laravel CRUD App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center mb-4">Daily Tasks Manager</h1>
                <p class="text-center">
                    Here you can view, create, edit, and delete your daily tasks.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Tasks List</h4>
                        <a href="{{ route('tasks.create') }}" class="btn btn-primary">Add New Task</a>
                    </div>
                    <div class="card-body">
                        @if($tasks->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tasks as $task)
                                            <tr>
                                                <td>{{ $task->id }}</td>
                                                <td>{{ $task->title ?? 'N/A' }}</td>
                                                <td>{{ Str::limit($task->description ?? 'No description', 50) }}</td>
                                                <td>
                                                    <span class="badge 
                                                                        @if($task->status == 'completed') 
                                                                            bg-success
                                                                        @elseif($task->status == 'in_progress') 
                                                                            bg-warning
                                                                        @else 
                                                                            bg-secondary
                                                                        @endif
                                                                    ">
                                                        {{ ucfirst(str_replace('_', ' ', $task->status ?? 'pending')) }}
                                                    </span>
                                                </td>
                                                <td>{{ $task->created_at ? $task->created_at->format('M d, Y') : 'N/A' }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('tasks.show', $task->id) }}"
                                                            class="btn btn-info btn-sm">View</a>
                                                        <a href="{{ route('tasks.edit', $task->id) }}"
                                                            class="btn btn-warning btn-sm">Edit</a>
                                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to delete this task?')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <h5>No tasks found</h5>
                                <p>You haven't created any tasks yet. <a href="{{ route('tasks.create') }}">Create your
                                        first task</a></p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>