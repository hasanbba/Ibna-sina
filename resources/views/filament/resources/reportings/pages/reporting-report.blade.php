<x-filament-panels::page>
    {{ $this->form }}

    @php
        $rows = $this->getReportRows();
        $totals = [
            'new' => $rows->sum('new'),
            'report' => $rows->sum('report'),
            'follow_up' => $rows->sum('follow_up'),
            'back' => $rows->sum('back'),
            'total' => $rows->sum('total'),
        ];
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
            <div class="overflow-x-auto">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #d1d5db; font-size: 0.875rem;">
                    <thead style="background-color: #f9fafb;">
                        <tr>
                            <th style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: left; font-weight: 600; color: #374151;">Consultant Name</th>
                            <th style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: left; font-weight: 600; color: #374151;">Department</th>
                            <th style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #374151;">New</th>
                            <th style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #374151;">Report</th>
                            <th style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #374151;">Follow-up</th>
                            <th style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #374151;">Back</th>
                            <th style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #374151;">Total</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #ffffff;">
                        @foreach ($rows as $row)
                            <tr>
                                <td style="border: 1px solid #d1d5db; padding: 12px 16px; color: #111827;">{{ $row['consultant_name'] }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 12px 16px; color: #4b5563;">{{ $row['department_name'] }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; color: #111827;">{{ number_format($row['new']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; color: #111827;">{{ number_format($row['report']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; color: #111827;">{{ number_format($row['follow_up']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; color: #111827;">{{ number_format($row['back']) }}</td>
                                <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($row['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background-color: #f9fafb;">
                        <tr>
                            <td style="border: 1px solid #d1d5db; padding: 12px 16px; font-weight: 600; color: #111827;">Grand Total</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px 16px; color: #6b7280;">All shown rows</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['new']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['report']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['follow_up']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['back']) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px 16px; text-align: right; font-weight: 600; color: #111827;">{{ number_format($totals['total']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
