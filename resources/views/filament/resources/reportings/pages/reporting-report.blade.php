<x-filament-panels::page>
    {{ $this->form }}

    @php
        $rows = $this->getReportRows();
        $isMonthlyReport = $this->isMonthlyReport();
        $isConsultantReport = $this->isConsultantReport();
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

    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getReportPeriodLabel() }}
        </x-slot>

        @if ($rows->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500">
                No reporting data found for the selected filters.
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                @if ($isMonthlyReport)
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-2 text-xs text-gray-500">
                        
                    </div>
                @endif
                <div style="width: 100%; overflow-x: {{ $isMonthlyReport ? 'scroll' : 'visible' }}; overflow-y: hidden; -webkit-overflow-scrolling: touch;">
                    <table style="width: {{ $isMonthlyReport ? 'max-content' : '100%' }}; min-width: {{ $isMonthlyReport ? '1400px' : '100%' }}; border-collapse: collapse; font-size: 0.8125rem;">
                    <thead style="background-color: #f9fafb;">
                        @if ($isMonthlyReport)
                            <tr>
                                <th rowspan="2" style="min-width: 210px; border: 1px solid #d1d5db; background-color: #f9fafb; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Consultant Name</th>
                                <th rowspan="2" style="min-width: 135px; border: 1px solid #d1d5db; background-color: #f9fafb; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Department</th>
                                <th colspan="4" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Doctors Performance Report</th>
                                <th colspan="5" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Patient Report</th>
                                <th colspan="4" style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; font-weight: 700; color: #111827;">Investigation</th>
                            </tr>
                        @endif
                        <tr>
                            @if ($isConsultantReport)
                                <th style="min-width: 120px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Date</th>
                            @endif
                            @unless ($isMonthlyReport)
                                <th style="min-width: 210px; border: 1px solid #d1d5db; background-color: #f9fafb; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Consultant Name</th>
                                <th style="min-width: 135px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Department</th>
                            @endunless
                            @if ($isMonthlyReport)
                                <th style="min-width: 86px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Chamber</th>
                                <th style="min-width: 82px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Off-day</th>
                                <th style="min-width: 74px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Leave</th>
                                <th style="min-width: 78px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Absent</th>
                            @endif
                            <th style="min-width: 64px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">New</th>
                            <th style="min-width: 78px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Report</th>
                            <th style="min-width: 92px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Follow-up</th>
                            <th style="min-width: 64px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Back</th>
                            <th style="min-width: 64px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Total</th>
                            @if ($isMonthlyReport)
                                <th style="min-width: 116px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Total Old PT</th>
                                <th style="min-width: 82px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">ID</th>
                                <th style="min-width: 112px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Parcentage %</th>
                                <th style="min-width: 96px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #374151;">Amount</th>
                            @endif
                            @if ($isConsultantReport)
                                <th style="min-width: 100px; border: 1px solid #d1d5db; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151;">Remark</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody style="background-color: #ffffff;">
                        @foreach ($rows as $row)
                            <tr>
                                @if ($isConsultantReport)
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['date'] }}</td>
                                @endif
                                <td style="min-width: 210px; border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['consultant_name'] }}</td>
                                <td style="min-width: 135px; border: 1px solid #d1d5db; padding: 10px 12px; color: #4b5563;">{{ $row['department_name'] }}</td>
                                @if ($isMonthlyReport)
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($row['chamber']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['off_day']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['leave']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['absent']) }}</td>
                                @endif
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['new']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['report']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['follow_up']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['back']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($row['total']) }}</td>
                                @if ($isMonthlyReport)
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['total_old_pt']) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['investigation_id'], 2) }}</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['investigation_percentage'], 2) }}%</td>
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; text-align: right; color: #111827;">{{ number_format($row['investigation_amount'], 2) }}</td>
                                @endif
                                @if ($isConsultantReport)
                                    <td style="border: 1px solid #d1d5db; padding: 10px 12px; color: #111827;">{{ $row['remark'] }}</td>
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
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
