<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">تقرير الحجوزات</h2>
                    <p class="text-sm text-gray-600">اختر الفترة ونوع الرحلة لعرض النتائج</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::button wire:click="generate" color="primary">عرض</x-filament::button>
                    <a href="{{ route('filament.reports.bookings.export', ['from_date' => $from_date, 'to_date' => $to_date, 'type' => $type]) }}" class="inline-flex items-center px-3 py-2 bg-gray-200 text-sm rounded text-gray-700" target="_blank">تصدير Excel</a>
                </div>
            </div>

            <form wire:submit.prevent="generate" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">من (تاريخ)</label>
                    <input type="date" wire:model="from_date" class="mt-1 block w-full rounded-md border-gray-300" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">إلى (تاريخ)</label>
                    <input type="date" wire:model="to_date" class="mt-1 block w-full rounded-md border-gray-300" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">نوع الرحلة</label>
                    <select wire:model="type" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="both">ذهاب وعودة</option>
                        <option value="go">ذهاب فقط</option>
                        <option value="return">عودة فقط</option>
                    </select>
                </div>
            </form>
        </x-filament::card>

        <x-filament::card>
            @if(!empty($results) && count($results) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">المستخدم</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">تاريخ الرحلة</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">نوع</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">من</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">إلى</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">مقاعد</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">أرقام المقاعد</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">سعر</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">إجمالي الحجز</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الحالة</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($results as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['user_name'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['date'] ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['type'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['from_city'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['to_city'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['seats'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['seat_numbers'] ?: '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['price'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['booking_total'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['current_status'] ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <div class="flex items-center gap-2">
                                            <select wire:model.defer="statusUpdates.{{ $row['booking_id'] }}" class="rounded border-gray-300 text-sm">
                                                @foreach(\App\Enums\BookingStatus::cases() as $s)
                                                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                                @endforeach
                                            </select>
                                            <x-filament::button size="sm" wire:click="updateBookingStatus({{ $row['booking_id'] }})">تحديث</x-filament::button>
                                            <x-filament::button size="sm" color="secondary" wire:click="openBooking({{ $row['booking_id'] }})">عرض التذكرة</x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-sm text-gray-500">لا توجد نتائج لعرضها.</div>
            @endif

            @if(!empty($results) && count($results) > 0)
                <div class="mt-4 text-right">
                    <span class="text-sm font-medium">إجمالي المقاعد: </span>
                    <span class="text-sm">{{ $total_seats }}</span>
                </div>
            @endif
        </x-filament::card>
    </div>

    {{-- Booking detail modal --}}
    @if($showBookingModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-3xl bg-white rounded shadow-lg p-6 mx-4">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-semibold">تفاصيل التذكرة</h3>
                    <button wire:click="closeBooking" class="text-gray-500">إغلاق</button>
                </div>

                @if($selectedBooking)
                    <div class="mt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><strong>اسم العميل:</strong> {{ $selectedBooking['customer_name'] }}</div>
                            <div><strong>الهاتف:</strong> {{ $selectedBooking['phone'] }}</div>
                            <div><strong>الحالة:</strong> {{ $selectedBooking['status'] }}</div>
                            <div><strong>الإجمالي:</strong> {{ $selectedBooking['total'] }}</div>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-medium">عناصر الحجز</h4>
                            <div class="overflow-x-auto mt-2">
                                <table class="w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="p-2">تاريخ</th>
                                            <th class="p-2">نوع</th>
                                            <th class="p-2">من</th>
                                            <th class="p-2">إلى</th>
                                            <th class="p-2">مقاعد</th>
                                            <th class="p-2">أرقام المقاعد</th>
                                            <th class="p-2">حافلة</th>
                                            <th class="p-2">سعر</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedBooking['items'] as $it)
                                            <tr>
                                                <td class="p-2">{{ $it['date'] ?? '-' }}</td>
                                                <td class="p-2">{{ $it['type'] }}</td>
                                                <td class="p-2">{{ $it['from'] }}</td>
                                                <td class="p-2">{{ $it['to'] }}</td>
                                                <td class="p-2">{{ $it['seats'] }}</td>
                                                <td class="p-2">{{ $it['seat_numbers'] ?: '-' }}</td>
                                                <td class="p-2">{{ $it['bus'] ?? '-' }}</td>
                                                <td class="p-2">{{ $it['price'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">لا توجد بيانات لهذه التذكرة.</p>
                @endif
            </div>
        </div>
    @endif

</x-filament-panels::page>
