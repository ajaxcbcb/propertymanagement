<div class="space-y-6 text-gray-900 dark:text-gray-100 p-1">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-gray-200 dark:border-gray-700 pb-6">
        <div>
            <h1 class="text-2xl font-bold">Tenant Account Statement</h1>
            <div class="mt-2 text-sm">
                <p class="font-bold uppercase tracking-tight">{{ $record->tenant->name }}</p>
                <p class="text-gray-500 dark:text-gray-400">{{ $record->property->name }}</p>
                <p class="text-xs text-gray-400 italic mt-1">{{ $record->property->address }}</p>
            </div>
        </div>
        
        <div class="text-right sm:min-w-[200px]">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Current Balance</p>
            <div @class([
                'text-3xl font-black mt-1',
                'text-red-600' => $balance > 0,
                'text-green-600' => $balance < 0,
                'text-gray-900 dark:text-white' => $balance == 0,
            ])>
                RM {{ number_format(abs($balance), 2) }}
            </div>
            <div class="mt-2">
                <span @class([
                    'px-3 py-1 rounded text-[10px] font-bold uppercase tracking-wider shadow-sm border',
                    'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/50 dark:text-red-200 dark:border-red-800' => $balance > 0,
                    'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/50 dark:text-green-200 dark:border-green-800' => $balance < 0,
                    'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700' => $balance == 0,
                ])>
                    @if($balance > 0) Overdue @elseif($balance < 0) Advance Payment @else On Track @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="p-4 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Total Charges</p>
            <p class="text-xl font-bold text-red-600">RM {{ number_format($totalCharges, 2) }}</p>
        </div>
        <div class="p-4 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Total Received</p>
            <p class="text-xl font-bold text-green-600">RM {{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="p-4 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm col-span-2 md:col-span-1">
            <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Statement Date</p>
            <p class="text-xl font-bold">{{ now()->format('d M Y') }}</p>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-[10px] font-bold tracking-widest border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-4">Date</th>
                        <th class="px-4 py-4">Description</th>
                        <th class="px-4 py-4 text-right">Debit</th>
                        <th class="px-4 py-4 text-right">Credit</th>
                        <th class="px-4 py-4 text-right bg-gray-50 dark:bg-gray-700/50">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php $runningBalance = 0; @endphp
                    @foreach($transactions as $tx)
                        @php 
                            $runningBalance += $tx['debit'] - $tx['credit'];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap text-gray-500">{{ $tx['date']->format('d M Y') }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $tx['description'] }}</span>
                                    @if(isset($tx['reference']))
                                        <span class="text-[10px] text-primary-600 dark:text-primary-400 font-mono mt-1">REF: {{ $tx['reference'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right text-red-600 font-mono font-medium">
                                {{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-4 text-right text-green-600 font-mono font-medium">
                                {{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '-' }}
                            </td>
                            <td @class([
                                'px-4 py-4 text-right font-bold font-mono bg-gray-50/50 dark:bg-gray-800/30',
                                'text-red-600' => $runningBalance > 0,
                                'text-green-600' => $runningBalance < 0,
                                'text-gray-900 dark:text-white' => $runningBalance == 0
                            ])>
                                {{ number_format($runningBalance, 2) }}
                                @if($runningBalance < 0) <span class="text-[10px]">CR</span> @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print Action -->
    <div class="flex justify-end no-print pt-6 pb-2">
        <button onclick="window.print()" class="px-6 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-900 rounded-lg text-xs font-bold uppercase tracking-widest transition-all hover:bg-gray-700 dark:hover:bg-white active:scale-95 shadow-md">
            Print Statement
        </button>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; color: black !important; }
    .dark { display: block !important; }
}
/* Force visibility for specific elements if Tailwind is being stubborn in modals */
.text-red-600 { color: #dc2626 !important; }
.text-green-600 { color: #16a34a !important; }
.bg-red-100 { background-color: #fee2e2 !important; }
.text-red-800 { color: #991b1b !important; }
</style>
