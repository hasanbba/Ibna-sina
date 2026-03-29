<?php

use App\Filament\Resources\Reportings\Pages\ReportingReport;
use App\Models\Consultant;
use App\Models\Department;
use App\Models\InOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/', function () {
    $today = Carbon::today();

    $todayInOuts = InOut::query()
        ->with(['consultant', 'department'])
        ->whereDate('date', $today)
        ->orderBy('in_time')
        ->get();

    $todayInOutsByConsultant = $todayInOuts->keyBy('consultant_id');

    $departments = Department::query()
        ->with(['consultants' => function ($query) {
            $query->orderBy('name');
        }])
        ->orderBy('name')
        ->get()
        ->map(function ($department) use ($todayInOutsByConsultant) {
            $consultantsInChamber = $department->consultants
                ->map(function ($consultant) use ($todayInOutsByConsultant) {
                    $inOut = $todayInOutsByConsultant->get($consultant->id);

                    if (! $inOut || blank($inOut->in_time) || filled($inOut->out_time)) {
                        return null;
                    }

                    return [
                        'name' => $consultant->name,
                        'in_time' => Carbon::parse($inOut->in_time)->format('h:i A'),
                    ];
                })
                ->filter()
                ->values();

            return [
                'name' => $department->name,
                'consultants' => $consultantsInChamber,
            ];
        })
        ->filter(fn (array $department): bool => $department['consultants']->isNotEmpty())
        ->values();

    $doctors = Consultant::query()
        ->with('department')
        ->orderBy('name')
        ->get();

    return view('chamber-board', [
        'today' => $today,
        'departments' => $departments,
        'todayInOuts' => $todayInOuts,
        'doctors' => $doctors,
    ]);
})->name('home');

Route::middleware('auth')->get('/reportings/print-preview', function (Request $request) {
    $page = app(ReportingReport::class);
    $page->data = $page->resolveFormData($request);

    $rows = $page->getReportRows();
    $isMonthlyReport = $page->isMonthlyReport();
    $isConsultantReport = $page->isConsultantReport();
    $totals = [
        'new' => $rows->sum('new'),
        'report' => $rows->sum('report'),
        'follow_up' => $rows->sum('follow_up'),
        'back' => $rows->sum('back'),
        'total' => $rows->sum('total'),
        'chamber' => $rows->sum('chamber'),
        'off_day' => $rows->sum('off_day'),
        'leave' => $rows->sum('leave'),
        'absent' => $rows->sum('absent'),
        'total_old_pt' => $rows->sum('total_old_pt'),
        'investigation_id' => $rows->sum('investigation_id'),
        'investigation_amount' => $rows->sum('investigation_amount'),
    ];

    $totals['investigation_percentage'] = $totals['total_old_pt'] > 0
        ? round(($totals['investigation_id'] * 100) / $totals['total_old_pt'], 2)
        : 0;

    return view('filament.resources.reportings.pages.reporting-report-print', [
        'page' => $page,
        'rows' => $rows,
        'isMonthlyReport' => $isMonthlyReport,
        'isConsultantReport' => $isConsultantReport,
        'totals' => $totals,
    ]);
})->name('reportings.print.preview');
