import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { DirectionProvider } from '@/components/ui/direction';
import { SidebarProvider } from '@/components/ui/sidebar';

type Props = {
    children: ReactNode;
    variant?: 'header' | 'sidebar';
};

export function AppShell({ children, variant = 'header' }: Props) {
    const isOpen = usePage().props.sidebarOpen;

    if (variant === 'header') {
        return (
            <DirectionProvider dir="rtl">
                <div className="flex min-h-screen w-full flex-col" >{children}</div>
            </DirectionProvider>
        );
    }

    return (
        <DirectionProvider dir="rtl">
            <SidebarProvider defaultOpen={isOpen}>{children}</SidebarProvider>
        </DirectionProvider>
    );
}
