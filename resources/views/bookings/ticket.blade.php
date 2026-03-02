<!doctype html>
<html lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تذكرة الحجز - #{{ $booking->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { display:flex; justify-content:space-between; align-items:center; }
        .items { margin-top:20px; border-collapse: collapse; width:100%; }
        .items th, .items td { border:1px solid #ddd; padding:8px; }
        .items th { background:#f4f4f4; }
        .actions { margin-top:20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2>تذكرة الحجز</h2>
                <div>رقم الحجز: <strong>#{{ $booking->id }}</strong></div>
                <div>اسم العميل: {{ $booking->customer_name }}</div>
                <div>رقم الهاتف: {{ $booking->phone_number }}</div>
            </div>
            <div>
                <div>الحالة: {{ $booking->status }}</div>
                <div>التاريخ: {{ $booking->created_at->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>نوع الرحلة</th>
                    <th>من</th>
                    <th>إلى</th>
                    <th>أرقام المقاعد</th>
                    <th>عدد المقاعد</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->items as $item)
                    <tr>
                        <td>{{ $item->type }}</td>
                        <td>{{ $item->from_city }}</td>
                        <td>{{ $item->to_city }}</td>
                        <td>{{ implode(', ', (array)$item->seat_numbers) }}</td>
                        <td>{{ $item->number_of_seats }}</td>
                        <td>{{ $item->price }} جنيه</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:16px; text-align:left;">
            <strong>الإجمالي: {{ $booking->total_price }} جنيه</strong>
        </div>

        <div class="actions">
            <button onclick="window.print()">طباعة التذكرة</button>
            <a href="/dashboard" style="margin-right:12px;">العودة إلى القائمة</a>
        </div>
    </div>

    <script>
        // افتح مربع الطباعة تلقائياً بعد تحميل الصفحة
        window.addEventListener('load', function () {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>
