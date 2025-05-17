@extends('layouts.staff-app')

@section('title', 'Work History')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <div class="mb-4 px-3 wrapper">
        <h2 class="text-dark">My Work History</h2>
    </div>

    <div class="px-3 wrapper">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Attendance History</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendanceHistory as $record)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($record->check_in)->format('h:i A') }}</td>
                                        <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('h:i A') : '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $record->status === 'Present' ? 'success' : ($record->status === 'Late' ? 'warning' : 'danger') }}">
                                                {{ $record->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($record->check_out)
                                                {{ \Carbon\Carbon::parse($record->check_out)->diffInHours(\Carbon\Carbon::parse($record->check_in)) }} hrs
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No attendance records found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Task History</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($taskHistory as $task)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($task->created_at)->format('M d, Y') }}</td>
                                        <td>{{ $task->reference_no }}</td>
                                        <td>{{ $task->service }}</td>
                                        <td>
                                            <span class="badge bg-{{ $task->status === 'Completed' ? 'success' : ($task->status === 'Processing' ? 'info' : 'warning') }}">
                                                {{ $task->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No task records found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wrapper {
        width: 1050px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .table {
        margin-bottom: 0;
    }
    .table th {
        font-weight: 600;
        color: #666;
        border-bottom: 1px solid #f0f0f0;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 1rem;
    }
</style>
@endsection