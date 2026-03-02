import { Head, usePage } from '@inertiajs/react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import TripForm from './trips/TripForm';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

export default function Dashboard({
    cities,
    reportgo,
    reportreturn,
    reporttotal,
}: {
    cities: {
        id: number;
        name: string;
    }[];
    reportgo: number;
    reportreturn: number;
    reporttotal: number;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative flex aspect-video items-center justify-center overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                        <div className="flex flex-col items-center justify-center p-4">
                            <p>تذاكر الذهاب</p>
                            <div className='flex gap-2'>
                            {reportgo}
                            <span>جنية</span>
                            </div>
                        </div>
                    </div>
                    <div className="relative flex aspect-video items-center justify-center overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <div className="flex flex-col items-center justify-center p-4">
                            <p>تذاكر العودة</p>
                            <div
                            className='flex gap-2'
                            >
                            {reportreturn}
                            <span>جنية</span>
                            </div>
                        </div>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="relative flex aspect-video items-center justify-center overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <div className="flex flex-col items-center justify-center p-4">
                            <p>إجمالي التذاكر</p>
                            <div className='gap-2 flex'>
                                {reporttotal}
                                <span>جنية</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    <div className="flex items-center justify-center">
                        <TripForm cities={cities} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
