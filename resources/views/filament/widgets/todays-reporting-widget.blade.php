<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
                <p class="text-sm text-gray-500">{{ $description }}</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a
                    href="{{ $reportingUrl }}"
                    class="fi-btn fi-size-md fi-labeled-from-sm inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition"
                >
                    {{ $reportingLabel }}
                </a>

                <a
                    href="{{ $inOutUrl }}"
                    class="fi-btn fi-size-md fi-labeled-from-sm inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition"
                >
                    {{ $inOutLabel }}
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
