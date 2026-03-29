<x-filament-panels::page>
    <style>
        .report-table-wrapper {
            width: 100%;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .report-table {
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .report-table .report-consultant {
            min-width: 210px;
        }

        .report-table .report-department {
            min-width: 135px;
        }

        .report-table .report-date {
            min-width: 120px;
        }

        .report-table .report-remark {
            min-width: 100px;
        }

        .report-table .report-number {
            white-space: nowrap;
        }

        .report-print-only {
            display: none;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
            }

            html,
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .fi-layout,
            .fi-main,
            .fi-main-ctn,
            .fi-page,
            .fi-page-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            body * {
                visibility: hidden;
            }

            .report-print-area,
            .report-print-area * {
                visibility: visible;
            }

            .report-print-area {
                position: static !important;
                inset: auto !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff;
                box-shadow: none !important;
                transform: none !important;
            }

            .report-print-hide {
                display: none !important;
            }

            .report-screen-only {
                display: none !important;
            }

            .report-print-only {
                display: block !important;
            }

            .report-print-area .fi-section {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .report-print-area .fi-section-header,
            .report-print-area .fi-section-header-heading,
            .report-print-area .fi-section-header-description,
            .report-print-area .fi-section-content-ctn {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }

            .report-print-area .fi-section-header {
                padding-bottom: 4px !important;
            }

            .report-print-area .overflow-hidden {
                overflow: visible !important;
                border: none !important;
                box-shadow: none !important;
            }

            .report-table-wrapper {
                overflow: visible !important;
            }

            .report-table {
                width: 100% !important;
                min-width: 0 !important;
                table-layout: fixed;
                font-size: 9px !important;
            }

            .report-table th,
            .report-table td {
                padding: 4px 5px !important;
                line-height: 1.2 !important;
                word-break: break-word;
                vertical-align: middle;
            }

            .report-table .report-consultant {
                min-width: 0 !important;
                width: 18% !important;
            }

            .report-table .report-department {
                min-width: 0 !important;
                width: 11% !important;
            }

            .report-table .report-date {
                min-width: 0 !important;
                width: 10% !important;
            }

            .report-table .report-remark {
                min-width: 0 !important;
                width: 10% !important;
            }

            .report-table .report-number {
                white-space: nowrap;
                word-break: normal;
            }

            .report-print-only .report-table {
                width: 104% !important;
                margin-left: -2% !important;
                font-size: 7.5px !important;
            }

            .report-print-only .report-table th,
            .report-print-only .report-table td {
                padding: 3px 4px !important;
            }

            .report-print-only .report-table .report-consultant {
                width: 16% !important;
            }

            .report-print-only .report-table .report-department {
                width: 9% !important;
            }
        }
    </style>

    @if ($this->showsFilters())
        {{ $this->form }}
    @endif

    @php
        $rows = $this->getReportRows();
        $isMonthlyReport = $this->isMonthlyReport();
        $isConsultantReport = $this->isConsultantReport();
        $isPresenceDailyWeeklyReport = $this->isPresenceDailyWeeklyReport();
        $isLateInEarlyOutReport = $this->isLateInEarlyOutReport();
        $isMonthlySummaryReport = $this->isMonthlySummaryReport();
        $monthlySummaryComparison = $isMonthlySummaryReport ? $this->getMonthlySummaryComparisonRows() : ['left' => [], 'right' => []];
        $monthlySummary = $isMonthlyReport ? $this->getMonthlySummary() : null;
        $presenceRows = $isPresenceDailyWeeklyReport ? $this->getPresenceDailyWeeklyRows() : collect();
        $presenceTotals = $isPresenceDailyWeeklyReport ? $this->getPresenceDailyWeeklyTotals() : [];
        $patientRows = $isPresenceDailyWeeklyReport ? $this->getPatientDailyWeeklyRows() : collect();
        $patientTotals = $isPresenceDailyWeeklyReport ? $this->getPatientDailyWeeklyTotals() : [];
        $lateInRows = $isLateInEarlyOutReport ? $this->getLateInRows() : collect();
        $earlyOutRows = $isLateInEarlyOutReport ? $this->getEarlyOutRows() : collect();
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
    @endphp

    <div class="report-print-hide mb-4 flex justify-end">
        @if ($this->usesBrowserPrint())
            <button
                type="button"
                onclick="window.print()"
                class="fi-btn fi-size-md fi-labeled-from-sm inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition"
            >
                Print
            </button>
        @else
            <a
                href="{{ $this->getPrintUrl() }}"
                target="_blank"
                rel="noopener noreferrer"
                class="fi-btn fi-size-md fi-labeled-from-sm inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition"
            >
                Open Print Page
            </a>
        @endif
    </div>

    <x-filament::section class="report-print-area">
        <x-slot name="heading">
            {{ $this->getReportPeriodLabel() }}
        </x-slot>

        @if ($isMonthlySummaryReport)
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="report-table-wrapper">
                    <table class="report-table" style="width: 100%; min-width: 1200px;">
                        <thead style="background-color: #d4d4d4;">
                            <tr>
                                <th rowspan="2" style="border: 1px solid #000; padding: 10px 12px; text-align: center; font-weight: 700;">Details</th>
                                <th colspan="2" style="border: 1px solid #000; padding: 10px 12px; text-align: center; font-weight: 700;">Total</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 10px 12px; text-align: center; font-weight: 700;">Details</th>
                                <th colspan="2" style="border: 1px solid #000; padding: 10px 12px; text-align: center; font-weight: 700;">Total</th>
                            </tr>
                            <tr>
                                <th style="border: 1px solid #000; padding: 8px 10px; text-align: center; font-weight: 700;">Previous</th>
                                <th style="border: 1px solid #000; padding: 8px 10px; text-align: center; font-weight: 700;">Current</th>
                                <th style="border: 1px solid #000; padding: 8px 10px; text-align: center; font-weight: 700;">Previous</th>
                                <th style="border: 1px solid #000; padding: 8px 10px; text-align: center; font-weight: 700;">Current</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < max(count($monthlySummaryComparison['left']), count($monthlySummaryComparison['right'])); $i++)
                                @php
                                    $leftRow = $monthlySummaryComparison['left'][$i] ?? null;
                                    $rightRow = $monthlySummaryComparison['right'][$i] ?? null;
                                @endphp
                                <tr>
                                    <td style="border: 1px solid #000; padding: 6px 8px;">{{ $leftRow['label'] ?? '' }}</td>
                                    <td style="border: 1px solid #000; padding: 6px 8px; text-align: right;">{{ isset($leftRow['previous']) ? number_format($leftRow['previous'], is_float($leftRow['previous']) ? 2 : 0) : '' }}</td>
                                    <td style="border: 1px solid #000; padding: 6px 8px; text-align: right;">{{ isset($leftRow['current']) ? number_format($leftRow['current'], is_float($leftRow['current']) ? 2 : 0) : '' }}</td>
                                    <td style="border: 1px solid #000; padding: 6px 8px;">{{ $rightRow['label'] ?? '' }}</td>
                                    <td style="border: 1px solid #000; padding: 6px 8px; text-align: right;">{{ isset($rightRow['previous']) ? number_format($rightRow['previous'], is_float($rightRow['previous']) ? 2 : 0) : '' }}</td>
                                    <td style="border: 1px solid #000; padding: 6px 8px; text-align: right;">{{ isset($rightRow['current']) ? number_format($rightRow['current'], is_float($rightRow['current']) ? 2 : 0) : '' }}</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($isPresenceDailyWeeklyReport)
            @if ($presenceRows->isEmpty() && $patientRows->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500">
                    No reporting data found for the selected filters.
                </div>
            @else
                <div class="space-y-10">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="report-table-wrapper">
                        <table class="report-table" style="width: 100%; min-width: 880px;">
                            <thead style="background-color: #f9fafb;">
                                <tr>
                                    <th colspan="9" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-size: 1.1rem; font-weight: 700; color: #111827;">
                                        Consultant&apos;s presence in daily and weekly basis
                                    </th>
                                </tr>
                                <tr>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Week</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Sat</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Sun</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Mon</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Tue</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Wed</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Thu</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Fri</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Total</th>
                                </tr>
                            </thead>
                            <tbody style="background-color: #ffffff;">
                                @foreach ($presenceRows as $row)
                                    <tr>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ $row['week_label'] }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['sat']) ? number_format($row['sat']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['sun']) ? number_format($row['sun']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['mon']) ? number_format($row['mon']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['tue']) ? number_format($row['tue']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['wed']) ? number_format($row['wed']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['thu']) ? number_format($row['thu']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['fri']) ? number_format($row['fri']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f9fafb;">
                                <tr>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Total</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['sat']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['sun']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['mon']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['tue']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['wed']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['thu']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['fri']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($presenceTotals['total']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="report-table-wrapper">
                        <table class="report-table" style="width: 100%; min-width: 880px;">
                            <thead style="background-color: #f9fafb;">
                                <tr>
                                    <th colspan="9" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-size: 1.1rem; font-weight: 700; color: #111827;">
                                        Number total patients in Daily &amp; weekly basis
                                    </th>
                                </tr>
                                <tr>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Week</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Sat</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Sun</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Mon</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Tue</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Wed</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Thu</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Fri</th>
                                    <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Total</th>
                                </tr>
                            </thead>
                            <tbody style="background-color: #ffffff;">
                                @foreach ($patientRows as $row)
                                    <tr>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ $row['week_label'] }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['sat']) ? number_format($row['sat']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['sun']) ? number_format($row['sun']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['mon']) ? number_format($row['mon']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['tue']) ? number_format($row['tue']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['wed']) ? number_format($row['wed']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['thu']) ? number_format($row['thu']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; color: #111827;">{{ filled($row['fri']) ? number_format($row['fri']) : '' }}</td>
                                        <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f9fafb;">
                                <tr>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Total</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['sat']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['sun']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['mon']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['tue']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['wed']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['thu']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['fri']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">{{ number_format($patientTotals['total']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                </div>
            @endif
        @elseif ($isLateInEarlyOutReport)
            @if ($lateInRows->isEmpty() && $earlyOutRows->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500">
                    No reporting data found for the selected filters.
                </div>
            @else
                <div class="space-y-10">
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="report-table-wrapper">
                            <table class="report-table" style="width: 100%; min-width: 760px;">
                                <thead style="background-color: #f9fafb;">
                                    <tr>
                                        <th colspan="5" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-size: 1.1rem; font-weight: 700; color: #111827;">
                                            Consultants Late In Report
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="report-consultant" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Consultants</th>
                                        <th class="report-department" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Department</th>
                                        <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Chamber Time</th>
                                        <th class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">In</th>
                                        <th class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Late In</th>
                                    </tr>
                                </thead>
                                <tbody style="background-color: #ffffff;">
                                    @forelse ($lateInRows as $row)
                                        <tr>
                                            <td class="report-consultant" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['consultant_name'] }}</td>
                                            <td class="report-department" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #4b5563;">{{ $row['department_name'] }}</td>
                                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['chamber_time'] }}</td>
                                            <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ $row['time'] }}</td>
                                            <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ $row['difference'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="border: 1px solid #d1d5db; padding: 16px 12px; text-align: center; color: #6b7280;">No late in records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="report-table-wrapper">
                            <table class="report-table" style="width: 100%; min-width: 760px;">
                                <thead style="background-color: #f9fafb;">
                                    <tr>
                                        <th colspan="5" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-size: 1.1rem; font-weight: 700; color: #111827;">
                                            Consultants Late Early Out
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="report-consultant" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Consultants</th>
                                        <th class="report-department" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Department</th>
                                        <th style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Chamber Time</th>
                                        <th class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Out</th>
                                        <th class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Early Out</th>
                                    </tr>
                                </thead>
                                <tbody style="background-color: #ffffff;">
                                    @forelse ($earlyOutRows as $row)
                                        <tr>
                                            <td class="report-consultant" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['consultant_name'] }}</td>
                                            <td class="report-department" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #4b5563;">{{ $row['department_name'] }}</td>
                                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['chamber_time'] }}</td>
                                            <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ $row['time'] }}</td>
                                            <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ $row['difference'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="border: 1px solid #d1d5db; padding: 16px 12px; text-align: center; color: #6b7280;">No early out records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @elseif ($rows->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500">
                No reporting data found for the selected filters.
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                @if ($isMonthlyReport)
                    <div class="report-print-hide border-b border-gray-200 bg-gray-50 px-4 py-2 text-xs text-gray-500">
                        Scroll horizontally to view all columns on screen. Print uses A4 landscape automatically.
                    </div>
                @endif
                <div class="report-screen-only report-table-wrapper" style="overflow-x: {{ $isMonthlyReport ? 'scroll' : 'visible' }};">
                    <table class="report-table" style="width: {{ $isMonthlyReport ? 'max-content' : '100%' }}; min-width: {{ $isMonthlyReport ? '1400px' : '100%' }};">
                    <thead style="background-color: #f9fafb;">
                        @if ($isMonthlyReport)
                            <tr>
                                <th rowspan="2" class="report-consultant" style="border: 1px solid #d1d5db; background-color: #f9fafb; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Consultant Name</th>
                                <th rowspan="2" class="report-department" style="border: 1px solid #d1d5db; background-color: #f9fafb; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Department</th>
                                <th colspan="4" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Doctors Performance Report</th>
                                <th colspan="5" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Patient Report</th>
                                <th colspan="4" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Investigation</th>
                            </tr>
                        @endif
                        <tr>
                            @if ($isConsultantReport)
                                <th class="report-date" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Date</th>
                            @endif
                            @unless ($isMonthlyReport)
                                <th class="report-consultant" style="border: 1px solid #d1d5db; background-color: #f9fafb; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Consultant Name</th>
                                <th class="report-department" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Department</th>
                            @endunless
                            @if ($isMonthlyReport)
                                <th class="report-number" style="min-width: 86px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Chamber</th>
                                <th class="report-number" style="min-width: 82px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Off-day</th>
                                <th class="report-number" style="min-width: 74px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Leave</th>
                                <th class="report-number" style="min-width: 78px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Absent</th>
                            @endif
                            <th class="report-number" style="min-width: 64px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">New</th>
                            <th class="report-number" style="min-width: 78px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Report</th>
                            <th class="report-number" style="min-width: 92px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Follow-up</th>
                            <th class="report-number" style="min-width: 64px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Back</th>
                            <th class="report-number" style="min-width: 64px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Total</th>
                            @if ($isMonthlyReport)
                                <th class="report-number" style="min-width: 116px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Total Old PT</th>
                                <th class="report-number" style="min-width: 82px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">ID</th>
                                <th class="report-number" style="min-width: 112px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Parcentage %</th>
                                <th class="report-number" style="min-width: 96px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Amount</th>
                            @endif
                            @if ($isConsultantReport)
                                <th class="report-remark" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Remark</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody style="background-color: #ffffff;">
                        @foreach ($rows as $row)
                            <tr>
                                @if ($isConsultantReport)
                                    <td class="report-date" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['date'] }}</td>
                                @endif
                                <td class="report-consultant" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['consultant_name'] }}</td>
                                <td class="report-department" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #4b5563;">{{ $row['department_name'] }}</td>
                                @if ($isMonthlyReport)
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($row['chamber']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['off_day']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['leave']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['absent']) }}</td>
                                @endif
                                <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['new']) }}</td>
                                <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['report']) }}</td>
                                <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['follow_up']) }}</td>
                                <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['back']) }}</td>
                                <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($row['total']) }}</td>
                                @if ($isMonthlyReport)
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['total_old_pt']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['investigation_id'], 2) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['investigation_percentage'], 2) }}%</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['investigation_amount'], 2) }}</td>
                                @endif
                                @if ($isConsultantReport)
                                    <td class="report-remark" style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['remark'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background-color: #f9fafb;">
                        <tr>
                            @if ($isConsultantReport)
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; color: #6b7280;">&nbsp;</td>
                            @endif
                            <td style="border: 1px solid #d1d5db; background-color: #f9fafb; padding: 10px 12px; font-weight: 600; color: #111827;">Grand Total</td>
                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; color: #6b7280;">All shown rows</td>
                            @if ($isMonthlyReport)
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['chamber']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['off_day']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['leave']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['absent']) }}</td>
                            @endif
                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['new']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['report']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['follow_up']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['back']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['total']) }}</td>
                            @if ($isMonthlyReport)
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['total_old_pt']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['investigation_id'], 2) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['investigation_percentage'], 2) }}%</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['investigation_amount'], 2) }}</td>
                            @endif
                            @if ($isConsultantReport)
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; color: #6b7280;">&nbsp;</td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
                </div>

                @if ($isMonthlyReport)
                    <div class="report-print-only">
                        <table class="report-table" style="width: 100%;">
                            <thead style="background-color: #f9fafb;">
                                <tr>
                                    <th class="report-consultant" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; font-weight: 700; color: #111827;">Consultant</th>
                                    <th class="report-department" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; font-weight: 700; color: #111827;">Dept.</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Ch.</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Off</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Lv.</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Abs.</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">New</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Rpt.</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">F-Up</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Back</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Total</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Old PT</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">ID</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">%</th>
                                    <th class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">Amt.</th>
                                </tr>
                            </thead>
                            <tbody style="background-color: #ffffff;">
                                @foreach ($rows as $row)
                                    <tr>
                                        <td class="report-consultant" style="border: 1px solid #d1d5db; padding: 8px 10px; color: #111827;">{{ $row['consultant_name'] }}</td>
                                        <td class="report-department" style="border: 1px solid #d1d5db; padding: 8px 10px; color: #4b5563;">{{ $row['department_name'] }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['chamber']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['off_day']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['leave']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['absent']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['new']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['report']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['follow_up']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['back']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($row['total']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['total_old_pt']) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['investigation_id'], 2) }}</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['investigation_percentage'], 2) }}%</td>
                                        <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; color: #111827;">{{ number_format($row['investigation_amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f9fafb;">
                                <tr>
                                    <td style="border: 1px solid #d1d5db; padding: 8px 10px; font-weight: 700; color: #111827;">Grand Total</td>
                                    <td style="border: 1px solid #d1d5db; padding: 8px 10px; color: #6b7280;">All rows</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['chamber']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['off_day']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['leave']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['absent']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['new']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['report']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['follow_up']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['back']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['total']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['total_old_pt']) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['investigation_id'], 2) }}</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['investigation_percentage'], 2) }}%</td>
                                    <td class="report-number" style="border: 1px solid #d1d5db; padding: 8px 10px; text-align: right; font-weight: 700; color: #111827;">{{ number_format($totals['investigation_amount'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
