<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $request->query('month', now()->format('Y-m'));
        $selectedMonth = Carbon::createFromFormat('!Y-m', $month);
        $employees = Employee::with(['attendances' => fn ($query) => $query
            ->whereBetween('date', [$selectedMonth->copy()->startOfMonth()->toDateString(), $selectedMonth->copy()->endOfMonth()->toDateString()])])
            ->orderBy('name')->get();
        $attendanceRecords = $employees->map(function ($employee) {
            $records = $employee->attendances;
            return (object) [
                'employee' => $employee,
                'days_present' => $records->map(fn ($record) => $record->date->toDateString())->unique()->count(),
                'on_time' => $records->filter(fn ($record) => strtolower($record->status) === 'present')->count(),
                'late_arrivals' => $records->filter(fn ($record) => strtolower($record->status) === 'late')->count(),
                'hours' => $records->sum(fn ($record) => $record->check_in && $record->check_out
                    ? max(0, $record->check_in->diffInMinutes($record->check_out)) / 60 : 0),
            ];
        });
        $active = Employee::whereRaw('LOWER(status) = ?', ['active']);
        $metrics = [
            'activeStaff' => (clone $active)->count(),
            'checkedIn' => Attendance::whereDate('date', today())->whereNotNull('check_in')->distinct()->count('employee_id'),
            'lateToday' => Attendance::whereDate('date', today())->whereRaw('LOWER(status) = ?', ['late'])->count(),
            'noCheckIn' => (clone $active)->whereDoesntHave('attendances', fn ($query) => $query->whereDate('date', today())->whereNotNull('check_in'))->count(),
        ];

        return view('admin.reports.attendance', compact('attendanceRecords', 'selectedMonth', 'month', 'metrics'));
    }

    public function filterByMonth(Request $request)
    {
        $request->validate(['month' => ['required', 'integer', 'between:1,12'], 'year' => ['required', 'integer', 'between:1900,2200']]);
        return redirect()->route('reports.attendance', ['month' => sprintf('%04d-%02d', $request->integer('year'), $request->integer('month'))]);
    }
}
