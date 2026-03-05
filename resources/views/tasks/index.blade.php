@extends('layouts/contentNavbarLayout')

@php
    $role = auth()->user()->role;
    $routePrefix = $role . '.tasks.';
@endphp


@section('title', 'Client')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-6">
                    <div class="container mt-5">
                        <div class="row">
                            <div class="col-md-12">
                                <h1 class="text-center mb-4">{{ __('Daily Tasks Manager') }}</h1>
                                <p class="text-center">
                                    {{ __('Here you can view, create, edit, and delete your daily tasks.') }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4>{{ __('Tasks List') }}</h4>
                                        <!-- <a href="{{ route('tasks.create') }}" class="btn btn-primary">Add New Task</a> -->
                                            @if(auth()->check() && auth()->user()->role === 'admin')
                                                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                                                    {{ __('Add New Task') }}
                                                </a>
                                            @endif
                                    </div>
                                    <div class="card-body">
                                        @if ($tasks->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('ID') }}</th>
                                                            <th>{{ __('Title') }}</th>
                                                            <th>{{ __('Description') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            <th>{{ __('Created At') }}</th>
                                                            <th>{{ __('Actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($tasks as $task)
                                                            <tr>
                                                                <td>{{ $task->id }}</td>
                                                                <td>{{ $task->title ?? 'N/A' }}</td>
                                                                <td>{{ Str::limit($task->description ?? 'No description', 50) }}
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge 
                                                                        @if ($task->status == 'completed') bg-success
                                                                        @elseif($task->status == 'in_progress') 
                                                                            bg-warning
                                                                        @else 
                                                                            bg-secondary @endif
                                                                    ">
                                                                        {{ ucfirst(str_replace('_', ' ', $task->status ?? 'pending')) }}
                                                                    </span>
                                                                </td>
                                                                <td>{{ $task->created_at ? $task->created_at->format('M d, Y') : 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group" role="group">
                                                                        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff', 'client']))
                                                                            <a href="{{ route('tasks.show', $task->id) }}"
                                                                                class="btn btn-info btn-sm">{{ __('View') }}</a>
                                                                        
                                                                        @endif

                                                                        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                                                                            <a href="{{ route('tasks.edit', $task->id) }}"
                                                                                class="btn btn-warning btn-sm">{{ __('Edit') }}</a>
                                                                        @endif

                                                                        @if(auth()->check() && auth()->user()->role === 'admin')
                                                                            <form
                                                                                action="{{ route('tasks.destroy', $task->id) }}"
                                                                                method="POST" style="display: inline;">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit"
                                                                                    class="btn btn-danger btn-sm"
                                                                                    onclick="return confirm('Are you sure you want to delete this task?')">
                                                                                    {{ __('Delete') }}
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info text-center">
                                                <h5>{{ __('No tasks found') }}</h5>
                                                <p>{{ __('You have not created any tasks yet.') }}<a
                                                        href="{{ route('tasks.create') }}">{{ __('Create your first task') }}</a></p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    @endsection
