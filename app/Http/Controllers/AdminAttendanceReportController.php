<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceReportController extends Controller
{
    public function index()
    {
        $totalStaff = Employee::count();

        $today = Carbon::today();
        $onDutyToday = Attendance::whereDate('date', $today)->count();
        $lateArrivals = Attendance::whereDate('date', $today)
                                ->where('status', 'Late')
                                ->count();
        $onLeave = $totalStaff - $onDutyToday;

        $attendanceRecords = Employee::with(['attendances' => function($query) {
            $query->whereMonth('date', Carbon::now()->month)
                  ->whereYear('date', Carbon::now()->year);
        }])->get()->map(function($employee) {
            $attendances = $employee->attendances;
            $daysPresent = $attendances->where('status', 'Present')->count();
            $lateArrivals = $attendances->where('status', 'Late')->count();
            $totalDays = Carbon::now()->daysInMonth;
            $daysWithAttendance = $attendances->count();
            $absences = $totalDays - $daysWithAttendance;
            $onTime = $daysPresent;
            $attendanceRate = $totalDays > 0 
                ? round((($daysPresent + $lateArrivals) / $totalDays) * 100) 
                : 0;

            return (object)[
                'employee' => $employee,
                'days_present' => $daysPresent + $lateArrivals, // Total days present including late
                'absences' => $absences,
                'late_arrivals' => $lateArrivals,
                'on_time' => $onTime,
                'attendance_rate' => $attendanceRate
            ];
        });

        return view('admin.reports.attendance', compact(
            'totalStaff',
            'onDutyToday',
            'lateArrivals',
            'onLeave',
            'attendanceRecords'
        ));
    }

    public function filterByMonth(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $date = Carbon::create($year, $month, 1);
        $totalDays = $date->daysInMonth;

        $attendanceRecords = Employee::with(['attendances' => function($query) use ($month, $year) {
            $query->whereMonth('date', $month)
                  ->whereYear('date', $year);
        }])->get()->map(function($employee) use ($totalDays) {
            $attendances = $employee->attendances;
            $daysPresent = $attendances->where('status', 'Present')->count();
            $lateArrivals = $attendances->where('status', 'Late')->count();
            $daysWithAttendance = $attendances->count();
            $absences = $totalDays - $daysWithAttendance;
            $onTime = $daysPresent;
            $attendanceRate = $totalDays > 0 
                ? round((($daysPresent + $lateArrivals) / $totalDays) * 100) 
                : 0;

            return (object)[
                'employee' => $employee,
                'days_present' => $daysPresent + $lateArrivals, // Total days present including late
                'absences' => $absences,
                'late_arrivals' => $lateArrivals,
                'on_time' => $onTime,
                'attendance_rate' => $attendanceRate
            ];
        });

        return response()->json($attendanceRecords);
    }
} 