
import { Head, Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Printer, Download } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types/navigation';

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'تقارير',
		href: dashboard(),
	},
	{
		title: 'تذاكر',
		href: dashboard(),
	},
];

export default function TicketReport({ ticketReport }: { ticketReport: any }) {
	const { url: currentUrl } = usePage();

	const pagination = ticketReport && ticketReport.last_page ? ticketReport : null;

	const buildPageHref = useMemo(() => {
		return (page: number) => {
			if (typeof window !== 'undefined') {
				const u = new URL(window.location.href);
				const params = u.searchParams;
				params.set('page', String(page));
				return `${u.pathname}?${params.toString()}`;
			}
			// fallback: preserve currentUrl if available
			if (currentUrl) {
				const split = currentUrl.split('?');
				return `${split[0]}?page=${page}`;
			}
			return `?page=${page}`;
		};
	}, [currentUrl]);
	const formatDate = (val: any) => {
		if (!val) return '-';
		const d = new Date(val);
		if (isNaN(d.getTime())) return String(val);
		return d.toLocaleDateString('ar-EG', {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
		});
	};

	const printTicket = (it: any) => {
		if (typeof window === 'undefined') return;
		const w = window.open('', '_blank');
		if (!w) return;

		const seats = (it.seat_numbers || []).join(', ') || '-';
		const date = formatDate(it.schedule_trip?.date);

		const html = `<!doctype html>
		<html lang="ar">
		<head>
		<meta charset="utf-8">
		<title>تذكرة</title>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<style>
		body{font-family:Arial,Helvetica,sans-serif;direction:rtl;padding:20px;color:#111}
		.table{width:100%;border-collapse:collapse}
		.table th,.table td{border:1px solid #ddd;padding:8px;text-align:right}
		.header{margin-bottom:16px}
		</style>
		</head>
		<body>
		<div class="header"><h2>تذكرة الحجز</h2></div>
		<table class="table">
		<tr><th>المستخدم</th><td>${it.booking?.customer_name ?? '-'}</td></tr>
		<tr><th>تاريخ الرحلة</th><td>${date}</td></tr>
		<tr><th>من</th><td>${it.from_city ?? '-'}</td></tr>
		<tr><th>إلى</th><td>${it.to_city ?? '-'}</td></tr>
		<tr><th>مقاعد</th><td>${it.number_of_seats ?? '-'}</td></tr>
		<tr><th>أرقام المقاعد</th><td>${seats}</td></tr>
		<tr><th>حافلة</th><td>${it.bus?.name ?? it.bus?.bus_name ?? '-'}</td></tr>
		<tr><th>سعر</th><td>${it.price ?? '-'}</td></tr>
		<tr><th>إجمالي الحجز</th><td>${it.booking?.total_price ?? '-'}</td></tr>
		</table>
		</body>
		</html>`;

		w.document.open();
		w.document.write(html);
		w.document.close();
		w.focus();
		setTimeout(() => {
			w.print();
			// optionally close after print
			// w.close();
		}, 300);
	};

	const [selectedTicket, setSelectedTicket] = useState<any>(null);

	const downloadTicket = (it: any) => {
		// For now we use same printable window and let user save as PDF via browser
		if (typeof window === 'undefined') return;
		const w = window.open('', '_blank');
		if (!w) return;

		const seats = (it.seat_numbers || []).join(', ') || '-';
		const date = formatDate(it.schedule_trip?.date);

		const html = `<!doctype html>
		<html lang="ar">
		<head>
		<meta charset="utf-8">
		<title>تذكرة</title>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<style>
		body{font-family:Arial,Helvetica,sans-serif;direction:rtl;padding:20px;color:#111}
		.table{width:100%;border-collapse:collapse}
		.table th,.table td{border:1px solid #ddd;padding:8px;text-align:right}
		.header{margin-bottom:16px}
		</style>
		</head>
		<body>
		<div class="header"><h2>تذكرة الحجز</h2></div>
		<table class="table">
		<tr><th>المستخدم</th><td>${it.booking?.customer_name ?? '-'}</td></tr>
		<tr><th>تاريخ الرحلة</th><td>${date}</td></tr>
		<tr><th>من</th><td>${it.from_city ?? '-'}</td></tr>
		<tr><th>إلى</th><td>${it.to_city ?? '-'}</td></tr>
		<tr><th>مقاعد</th><td>${it.number_of_seats ?? '-'}</td></tr>
		<tr><th>أرقام المقاعد</th><td>${seats}</td></tr>
		<tr><th>حافلة</th><td>${it.bus?.name ?? it.bus?.bus_name ?? '-'}</td></tr>
		<tr><th>سعر</th><td>${it.price ?? '-'}</td></tr>
		<tr><th>إجمالي الحجز</th><td>${it.booking?.total_price ?? '-'}</td></tr>
		</table>
		</body>
		</html>`;

		w.document.open();
		w.document.write(html);
		w.document.close();
		w.focus();
		setTimeout(() => {
			w.print();
		}, 300);
	};

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="تقرير التذاكر" />
			<div className="p-4">
				<div dir="rtl" className="overflow-x-auto rounded-lg border border-sidebar-border/70">
					<table className="w-full table-auto min-w-[900px] border-collapse">
						<thead className="bg-gray-50">
							<tr>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 border border-gray-200 w-64">المستخدم</th>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 w-40 border border-gray-200">تاريخ الرحلة</th>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 w-28 border border-gray-200">نوع</th>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 w-32 border border-gray-200">من</th>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 w-32 border border-gray-200">إلى</th>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 w-20 border border-gray-200">مقاعد</th>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 w-48 border border-gray-200">أرقام المقاعد</th>
								<th className="px-4 py-3 text-right text-xs font-medium text-gray-500 w-40 border border-gray-200">حافلة</th>
								<th className="px-4 py-3 text-left text-xs font-medium text-gray-500 w-24 border border-gray-200">سعر</th>
								<th className="px-4 py-3 text-left text-xs font-medium text-gray-500 w-28 border border-gray-200">إجمالي الحجز</th>
								<th className="px-4 py-3 text-center text-xs font-medium text-gray-500 w-24 border border-gray-200">طباعة</th>
							</tr>
						</thead>
						<tbody className="bg-white">
							{(ticketReport?.data ?? ticketReport ?? []).map((it: any) => (
								<tr key={it.id} className="odd:bg-white even:bg-muted">
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top border border-gray-200 whitespace-normal break-words">
										{it.booking?.customer_name ?? it.booking?.user?.name ?? it.booking?.user?.email ?? '-'}
									</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top border border-gray-200">{formatDate(it.schedule_trip?.date)}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top border border-gray-200">{it.type ?? '-'}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top border border-gray-200">{it.from_city}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top border border-gray-200">{it.to_city}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top border border-gray-200">{it.number_of_seats}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top border border-gray-200">
										<div className="max-w-[320px] whitespace-normal break-words">{(it.seat_numbers || []).join(', ') || '-'}</div>
									</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-right align-top break-words border border-gray-200">{it.bus?.name ?? it.bus?.bus_name ?? '-'}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-left align-top border border-gray-200">{it.price}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-left align-top border border-gray-200">{it.booking?.total_price ?? '-'}</td>
									<td className="px-4 py-2 text-sm text-gray-700 text-center align-top border border-gray-200 flex items-center justify-center gap-2">
										<Button variant="ghost" onClick={() => { setSelectedTicket(it); printTicket(it); }}>
											<Printer className="h-4 w-4 ml-2" />
											<span>طباعة</span>
										</Button>
										<Button variant="ghost" onClick={() => { setSelectedTicket(it); downloadTicket(it); }}>
											<Download className="h-4 w-4 ml-2" />
											<span>تحميل PDF</span>
										</Button>
									</td>
								</tr>
							))}
						</tbody>
					</table>
					{pagination && pagination.last_page > 1 && (
						<nav className="mt-3 flex items-center justify-center gap-2 text-sm" aria-label="Pagination">
							<Link
								href={buildPageHref(Math.max(1, (pagination.current_page || 1) - 1))}
								className={`px-3 py-1 border rounded ${!pagination.prev_page_url ? 'opacity-50 pointer-events-none' : ''}`}
							>
								السابق
							</Link>
							{Array.from({ length: pagination.last_page }).map((_, i) => {
								const page = i + 1;
								return (
									<Link
										key={page}
										href={buildPageHref(page)}
										className={`px-3 py-1 border rounded ${pagination.current_page === page ? 'bg-gray-200' : ''}`}
									>
										{page}
									</Link>
								);
							})}
							<Link
								href={buildPageHref(Math.min(pagination.last_page, (pagination.current_page || 1) + 1))}
								className={`px-3 py-1 border rounded ${!pagination.next_page_url ? 'opacity-50 pointer-events-none' : ''}`}
							>
								التالي
							</Link>
						</nav>
					)}

					{/* Fixed animated download button (appears when a ticket is selected) */}
					{/* {selectedTicket && (
						<>
							<style>{`@keyframes slideX { 0% { transform: translateX(0); } 50% { transform: translateX(180px); } 100% { transform: translateX(0); } }`}</style>
							<button
								onClick={() => downloadTicket(selectedTicket)}
								className="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded shadow-lg"
								style={{ position: 'fixed', bottom: 24, left: 24, zIndex: 60, animation: 'slideX 4s linear infinite' }}
							>
								<Download className="h-4 w-4" />
								<span>تحميل التذكرة المحددة</span>
							</button>
						</>
					)} */}
				</div>
			</div>
		</AppLayout>
	);
}

