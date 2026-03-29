<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->getReportPeriodLabel() }}</title>
    <style>
        body {
            margin: 0;
            padding: 18px;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #111827;
        }

        .print-shell {
            max-width: 100%;
            margin: 0 auto;
        }

        .print-actions {
            margin-bottom: 12px;
            text-align: right;
        }

        .print-button {
            display: inline-block;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 14px;
            background: #ffffff;
            color: #111827;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .print-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
        }

        .print-title {
            margin: 0 0 10px;
            font-size: 20px;
            font-weight: 700;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .print-table th,
        .print-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .print-table thead th {
            background: #f9fafb;
            font-weight: 700;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
            white-space: nowrap;
        }

        .w-consultant {
            width: 16%;
        }

        .w-department {
            width: 9%;
        }

        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .print-actions {
                display: none;
            }

            .print-card {
                border: none;
                border-radius: 0;
                padding: 0;
            }

            .print-title {
                margin-bottom: 6px;
                font-size: 16px;
            }

            .print-table {
                font-size: 8px;
                table-layout: fixed;
            }

            .print-table th,
            .print-table td {
                padding: 3px 4px;
                line-height: 1.15;
            }

            .w-consultant {
                width: 15%;
            }

            .w-department {
                width: 8%;
            }
        }
    </style>
</head>
<body>
    <div class="print-shell">
        <div class="print-actions">
            <button type="button" class="print-button" onclick="window.print()">Print</button>
        </div>

        <div class="print-card">
            <h1 class="print-title">{{ $page->getReportPeriodLabel() }}</h1>

            @if ($page->isMonthlySummaryReport())
                @php
                    $monthlySummaryComparison = $page->getMonthlySummaryComparisonRows();
                @endphp

                <table class="print-table">
                    <thead>
                        <tr>
                            <th rowspan="2">Details</th>
                            <th colspan="2">Total</th>
                            <th rowspan="2">Details</th>
                            <th colspan="2">Total</th>
                        </tr>
                        <tr>
                            <th>Previous</th>
                            <th>Current</th>
                            <th>Previous</th>
                            <th>Current</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < max(count($monthlySummaryComparison['left']), count($monthlySummaryComparison['right'])); $i++)
                            @php
                                $leftRow = $monthlySummaryComparison['left'][$i] ?? null;
                                $rightRow = $monthlySummaryComparison['right'][$i] ?? null;
                            @endphp
                            <tr>
                                <td class="text-left">{{ $leftRow['label'] ?? '' }}</td>
                                <td class="text-right">{{ isset($leftRow['previous']) ? number_format($leftRow['previous'], is_float($leftRow['previous']) ? 2 : 0) : '' }}</td>
                                <td class="text-right">{{ isset($leftRow['current']) ? number_format($leftRow['current'], is_float($leftRow['current']) ? 2 : 0) : '' }}</td>
                                <td class="text-left">{{ $rightRow['label'] ?? '' }}</td>
                                <td class="text-right">{{ isset($rightRow['previous']) ? number_format($rightRow['previous'], is_float($rightRow['previous']) ? 2 : 0) : '' }}</td>
                                <td class="text-right">{{ isset($rightRow['current']) ? number_format($rightRow['current'], is_float($rightRow['current']) ? 2 : 0) : '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            @elseif ($page->isPresenceDailyWeeklyReport())
                @php
                    $presenceRows = $page->getPresenceDailyWeeklyRows();
                    $presenceTotals = $page->getPresenceDailyWeeklyTotals();
                    $patientRows = $page->getPatientDailyWeeklyRows();
                    $patientTotals = $page->getPatientDailyWeeklyTotals();
                @endphp

                <table class="print-table">
                    <thead>
                        <tr>
                            <th colspan="9">Consultant&apos;s presence in daily and weekly basis</th>
                        </tr>
                        <tr>
                            <th>Week</th>
                            <th>Sat</th>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($presenceRows as $row)
                            <tr>
                                <td class="text-left"><strong>{{ $row['week_label'] }}</strong></td>
                                <td class="text-right">{{ filled($row['sat']) ? number_format($row['sat']) : '' }}</td>
                                <td class="text-right">{{ filled($row['sun']) ? number_format($row['sun']) : '' }}</td>
                                <td class="text-right">{{ filled($row['mon']) ? number_format($row['mon']) : '' }}</td>
                                <td class="text-right">{{ filled($row['tue']) ? number_format($row['tue']) : '' }}</td>
                                <td class="text-right">{{ filled($row['wed']) ? number_format($row['wed']) : '' }}</td>
                                <td class="text-right">{{ filled($row['thu']) ? number_format($row['thu']) : '' }}</td>
                                <td class="text-right">{{ filled($row['fri']) ? number_format($row['fri']) : '' }}</td>
                                <td class="text-right"><strong>{{ number_format($row['total']) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-left"><strong>Total</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['sat']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['sun']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['mon']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['tue']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['wed']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['thu']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['fri']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($presenceTotals['total']) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div style="height: 28px;"></div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th colspan="9">Number total patients in Daily &amp; weekly basis</th>
                        </tr>
                        <tr>
                            <th>Week</th>
                            <th>Sat</th>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patientRows as $row)
                            <tr>
                                <td class="text-left"><strong>{{ $row['week_label'] }}</strong></td>
                                <td class="text-right">{{ filled($row['sat']) ? number_format($row['sat']) : '' }}</td>
                                <td class="text-right">{{ filled($row['sun']) ? number_format($row['sun']) : '' }}</td>
                                <td class="text-right">{{ filled($row['mon']) ? number_format($row['mon']) : '' }}</td>
                                <td class="text-right">{{ filled($row['tue']) ? number_format($row['tue']) : '' }}</td>
                                <td class="text-right">{{ filled($row['wed']) ? number_format($row['wed']) : '' }}</td>
                                <td class="text-right">{{ filled($row['thu']) ? number_format($row['thu']) : '' }}</td>
                                <td class="text-right">{{ filled($row['fri']) ? number_format($row['fri']) : '' }}</td>
                                <td class="text-right"><strong>{{ number_format($row['total']) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-left"><strong>Total</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['sat']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['sun']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['mon']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['tue']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['wed']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['thu']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['fri']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($patientTotals['total']) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            @elseif ($page->isLateInEarlyOutReport())
                @php
                    $lateInRows = $page->getLateInRows();
                    $earlyOutRows = $page->getEarlyOutRows();
                @endphp

                <table class="print-table">
                    <thead>
                        <tr>
                            <th colspan="5">Consultants Late In Report</th>
                        </tr>
                        <tr>
                            <th class="text-left w-consultant">Consultants</th>
                            <th class="text-left w-department">Department</th>
                            <th class="text-left">Chamber Time</th>
                            <th class="text-right">In</th>
                            <th class="text-right">Late In</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lateInRows as $row)
                            <tr>
                                <td class="text-left">{{ $row['consultant_name'] }}</td>
                                <td class="text-left">{{ $row['department_name'] }}</td>
                                <td class="text-left">{{ $row['chamber_time'] }}</td>
                                <td class="text-right">{{ $row['time'] }}</td>
                                <td class="text-right">{{ $row['difference'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-left">No late in records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="height: 28px;"></div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th colspan="5">Consultants Late Early Out</th>
                        </tr>
                        <tr>
                            <th class="text-left w-consultant">Consultants</th>
                            <th class="text-left w-department">Department</th>
                            <th class="text-left">Chamber Time</th>
                            <th class="text-right">Out</th>
                            <th class="text-right">Early Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($earlyOutRows as $row)
                            <tr>
                                <td class="text-left">{{ $row['consultant_name'] }}</td>
                                <td class="text-left">{{ $row['department_name'] }}</td>
                                <td class="text-left">{{ $row['chamber_time'] }}</td>
                                <td class="text-right">{{ $row['time'] }}</td>
                                <td class="text-right">{{ $row['difference'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-left">No early out records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="print-table">
                    <thead>
                        @if ($isMonthlyReport)
                            <tr>
                                <th rowspan="2" class="text-left w-consultant">Consultant</th>
                                <th rowspan="2" class="text-left w-department">Department</th>
                                <th colspan="4">Doctors Performance Report</th>
                                <th colspan="5">Patient Report</th>
                                <th colspan="4">Investigation</th>
                            </tr>
                        @endif
                        <tr>
                            @if ($isConsultantReport)
                                <th class="text-left">Date</th>
                            @endif
                            @unless ($isMonthlyReport)
                                <th class="text-left w-consultant">Consultant</th>
                                <th class="text-left w-department">Department</th>
                            @endunless
                            @if ($isMonthlyReport)
                                <th class="text-right">Chamber</th>
                                <th class="text-right">Off-Day</th>
                                <th class="text-right">Leave</th>
                                <th class="text-right">Absent</th>
                            @endif
                            <th class="text-right">New</th>
                            <th class="text-right">Report</th>
                            <th class="text-right">Follow up</th>
                            <th class="text-right">Left</th>
                            <th class="text-right">Total</th>
                            @if ($isMonthlyReport)
                                <th class="text-right">Total Old PT</th>
                                <th class="text-right">ID</th>
                                <th class="text-right">Parcentage %</th>
                                <th class="text-right">Amount</th>
                            @endif
                            @if ($isConsultantReport)
                                <th class="text-left">Remark</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @if ($isConsultantReport)
                                    <td class="text-left">{{ $row['date'] }}</td>
                                @endif
                                <td class="text-left">{{ $row['consultant_name'] }}</td>
                                <td class="text-left">{{ $row['department_name'] }}</td>
                                @if ($isMonthlyReport)
                                    <td class="text-right">{{ number_format($row['chamber']) }}</td>
                                    <td class="text-right">{{ number_format($row['off_day']) }}</td>
                                    <td class="text-right">{{ number_format($row['leave']) }}</td>
                                    <td class="text-right">{{ number_format($row['absent']) }}</td>
                                @endif
                                <td class="text-right">{{ number_format($row['new']) }}</td>
                                <td class="text-right">{{ number_format($row['report']) }}</td>
                                <td class="text-right">{{ number_format($row['follow_up']) }}</td>
                                <td class="text-right">{{ number_format($row['back']) }}</td>
                                <td class="text-right">{{ number_format($row['total']) }}</td>
                                @if ($isMonthlyReport)
                                    <td class="text-right">{{ number_format($row['total_old_pt']) }}</td>
                                    <td class="text-right">{{ number_format($row['investigation_id'], 2) }}</td>
                                    <td class="text-right">{{ number_format($row['investigation_percentage'], 2) }}%</td>
                                    <td class="text-right">{{ number_format($row['investigation_amount'], 2) }}</td>
                                @endif
                                @if ($isConsultantReport)
                                    <td class="text-left">{{ $row['remark'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            @if ($isConsultantReport)
                                <td>&nbsp;</td>
                            @endif
                            <td class="text-left"><strong>Grand Total</strong></td>
                            <td class="text-left">All rows</td>
                            @if ($isMonthlyReport)
                                <td class="text-right"><strong>{{ number_format($totals['chamber']) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totals['off_day']) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totals['leave']) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totals['absent']) }}</strong></td>
                            @endif
                            <td class="text-right"><strong>{{ number_format($totals['new']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totals['report']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totals['follow_up']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totals['back']) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totals['total']) }}</strong></td>
                            @if ($isMonthlyReport)
                                <td class="text-right"><strong>{{ number_format($totals['total_old_pt']) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totals['investigation_id'], 2) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totals['investigation_percentage'], 2) }}%</strong></td>
                                <td class="text-right"><strong>{{ number_format($totals['investigation_amount'], 2) }}</strong></td>
                            @endif
                            @if ($isConsultantReport)
                                <td>&nbsp;</td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>
</body>
</html>
