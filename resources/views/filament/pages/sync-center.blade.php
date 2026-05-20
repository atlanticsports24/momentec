<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @if($this->activeRun)
            <x-filament::section heading="Current run" wire:poll.3s>
                <dl class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                    <div><span class="font-medium">ID:</span> {{ $this->activeRun->id }}</div>
                    <div><span class="font-medium">Status:</span> {{ $this->activeRun->status }}</div>
                    <div><span class="font-medium">Step:</span> {{ $this->activeRun->current_step ?? '—' }}</div>
                    <div><span class="font-medium">Mode:</span> {{ $this->activeRun->mode }}</div>
                    <div><span class="font-medium">Progress:</span> {{ $this->activeRun->progressPercent() }}%</div>
                    <div><span class="font-medium">Rows:</span> {{ $this->activeRun->processed_rows }} / {{ $this->activeRun->total_rows }}</div>
                    <div><span class="font-medium">Errors:</span> {{ $this->activeRun->error_count }}</div>
                </dl>
                @if($this->activeRun->error_sample)
                    <pre class="mt-4 max-h-48 overflow-auto rounded bg-gray-950 p-3 text-xs text-gray-100 dark:bg-gray-900">{{ json_encode($this->activeRun->error_sample, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </x-filament::section>
        @endif

        <x-filament::section heading="Recent sync runs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="p-2">#</th>
                            <th class="p-2">Mode</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Step</th>
                            <th class="p-2">%</th>
                            <th class="p-2">Rows</th>
                            <th class="p-2">Started</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\SyncRun::query()->latest()->limit(15)->get() as $r)
                            <tr class="border-b border-gray-100 dark:border-gray-800" wire:key="run-{{ $r->id }}">
                                <td class="p-2">{{ $r->id }}</td>
                                <td class="p-2">{{ $r->mode }}</td>
                                <td class="p-2">{{ $r->status }}</td>
                                <td class="p-2">{{ $r->current_step ?? '—' }}</td>
                                <td class="p-2">{{ $r->progressPercent() }}</td>
                                <td class="p-2">{{ $r->processed_rows }} / {{ $r->total_rows }}</td>
                                <td class="p-2">{{ optional($r->started_at)->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
