import { Head, router } from '@inertiajs/react';
import { Bus, Calendar, MapPin, Users, Check } from 'lucide-react';
import React, { useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { toast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types/navigation';

interface Bus {
    bus_id: number;
    bus_name: string;
    plate_number: string;
    total_seats: number;
    available_seats: number;
    booked_seats: number;
    can_book: boolean;
}

interface TripResult {
    available: boolean;
    message: string;
    schedule_trip_id: number;
    date: string;
    from_city: string;
    to_city: string;
    buses: Bus[];
}

interface SearchResult {
    success: boolean;
    message?: string;
    trip: {
        id: number;
        from_city: string;
        to_city: string;
        price: number;
        round_trip_price: number | null;
    };
    passenger_count: number;
    trip_type: string;
    total_price: number;
    results: {
        go_trip?: TripResult;
        return_trip?: TripResult;
    };
}

interface Props {
    searchData: string;
    results: string;
}

const TripResults = ({ results }: Props) => {
    
    const parsedResults: SearchResult = JSON.parse(results);
    const isRoundTrip = parsedResults.trip_type === 'goreturn';

    // State لتخزين الأوتوبيسات المختارة في حالة الذهاب والعودة
    const [selectedGoBus, setSelectedGoBus] = useState<Bus | null>(null);
    const [selectedReturnBus, setSelectedReturnBus] = useState<Bus | null>(null);

    const getTripTypeLabel = (type: string) => {
        switch (type) {
            case 'go':
                return 'ذهاب فقط';
            case 'return':
                return 'عودة فقط';
            case 'goreturn':
                return 'ذهاب وعودة';
            default:
                return type;
        }
    };

    const renderBusCard = (bus: Bus, tripType: 'go' | 'return') => {
        const isSelected = tripType === 'go'
            ? selectedGoBus?.bus_id === bus.bus_id
            : selectedReturnBus?.bus_id === bus.bus_id;

        return (
            <Card key={bus.bus_id} className={`mb-4 ${isSelected ? 'ring-2 ring-primary' : ''}`}>
                <CardHeader>
                    <div className="flex justify-between items-start">
                        <div>
                            <CardTitle className="flex items-center gap-2">
                                <Bus className="h-5 w-5" />
                                {bus.bus_name}
                                {isSelected && <Check className="h-5 w-5 text-primary" />}
                            </CardTitle>
                            <CardDescription>رقم اللوحة: {bus.plate_number}</CardDescription>
                        </div>
                        <Badge variant={bus.can_book ? 'default' : 'secondary'}>
                            {bus.can_book ? 'متاح' : 'غير متاح'}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="space-y-2">
                        <div className="flex justify-between text-sm">
                            <span className="text-muted-foreground">إجمالي المقاعد:</span>
                            <span className="font-medium">{bus.total_seats}</span>
                        </div>
                        <div className="flex justify-between text-sm">
                            <span className="text-muted-foreground">المقاعد المحجوزة:</span>
                            <span className="font-medium">{bus.booked_seats}</span>
                        </div>
                        <div className="flex justify-between text-sm">
                            <span className="text-muted-foreground">المقاعد المتاحة:</span>
                            <span className="font-medium text-green-600">{bus.available_seats}</span>
                        </div>
                        <Separator className="my-2" />
                        <Button
                            className="w-full"
                            disabled={!bus.can_book}
                            onClick={() => handleBusSelection(bus, tripType)}
                            variant={isSelected ? 'default' : 'outline'}
                        >
                            {isRoundTrip ? (isSelected ? 'تم الاختيار' : 'اختر') : 'احجز الآن'}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        );
    };

    const handleBusSelection = (bus: Bus, tripType: 'go' | 'return') => {
        if (isRoundTrip) {
            // في حالة الذهاب والعودة، نخزن الاختيار
            if (tripType === 'go') {
                setSelectedGoBus(bus);
            } else {
                setSelectedReturnBus(bus);
            }
        } else {
            // في حالة رحلة واحدة، نحجز مباشرة
            handleBooking(bus, tripType);
        }
    };

    const handleBooking = (bus: Bus, tripType: 'go' | 'return') => {
        // تحديد بيانات الرحلة بناءً على النوع
        const tripResult = tripType === 'go' ? parsedResults.results.go_trip : parsedResults.results.return_trip;

        if (!tripResult) {
            return;
        }

        // إعداد بيانات الرحلة للتمرير إلى صفحة اختيار المقاعد
        const tripData = {
            schedule_trip_id: tripResult.schedule_trip_id,
            bus_id: bus.bus_id,
            type: tripType === 'go' ? 'go_way' : 'return_way',
            price: parsedResults.trip.price,
            from_city: tripResult.from_city,
            to_city: tripResult.to_city,
            date: tripResult.date,
            passenger_count: parsedResults.passenger_count,
        };

        // التوجيه إلى صفحة اختيار المقاعد
        router.visit('/seat-selection', {
            method: 'get',
            data: {
                schedule_trip_id: tripResult.schedule_trip_id,
                bus_id: bus.bus_id,
                trip_data: JSON.stringify(tripData),
            },
        });
    };

    const handleRoundTripBooking = () => {
        if (!selectedGoBus || !selectedReturnBus) {
            toast({
                variant: 'destructive',
                title: 'تنبيه',
                description: 'يرجى اختيار أوتوبيس للذهاب وأوتوبيس للعودة',
            });
            return;
        }

        const goTripResult = parsedResults.results.go_trip;
        const returnTripResult = parsedResults.results.return_trip;

        if (!goTripResult || !returnTripResult) {
            return;
        }

        // إعداد بيانات الرحلتين
        const tripData = {
            trip_type: 'round_trip',
            passenger_count: parsedResults.passenger_count,
            go_trip: {
                schedule_trip_id: goTripResult.schedule_trip_id,
                bus_id: selectedGoBus.bus_id,
                bus_name: selectedGoBus.bus_name,
                type: 'go_way',
                price: parsedResults.trip.price,
                from_city: goTripResult.from_city,
                to_city: goTripResult.to_city,
                date: goTripResult.date,
            },
            return_trip: {
                schedule_trip_id: returnTripResult.schedule_trip_id,
                bus_id: selectedReturnBus.bus_id,
                bus_name: selectedReturnBus.bus_name,
                type: 'return_way',
                price: parsedResults.trip.round_trip_price || parsedResults.trip.price,
                from_city: returnTripResult.from_city,
                to_city: returnTripResult.to_city,
                date: returnTripResult.date,
            },
        };

        // التوجيه إلى صفحة اختيار المقاعد للرحلة الأولى (الذهاب)
        router.visit('/round-trip-seat-selection', {
            method: 'get',
            data: {
                schedule_trip_id: goTripResult.schedule_trip_id,
                bus_id: selectedGoBus.bus_id,
                trip_data: JSON.stringify(tripData),
            },
        });
    };

    const renderTripSection = (tripResult: TripResult, title: string, tripType: 'go' | 'return') => (
        <div className="mb-8">
            <h2 className="text-2xl font-bold mb-4">{title}</h2>
            <Card className="mb-4">
                <CardHeader>
                    <div className="space-y-2">
                        <div className="flex items-center gap-2 text-sm">
                            <MapPin className="h-4 w-4" />
                            <span>من: {tripResult.from_city}</span>
                            <span className="mx-2">←</span>
                            <span>إلى: {tripResult.to_city}</span>
                        </div>
                        <div className="flex items-center gap-2 text-sm">
                            <Calendar className="h-4 w-4" />
                            <span>التاريخ: {tripResult.date}</span>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            {tripResult.available && tripResult.buses.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {tripResult.buses.map((bus) => renderBusCard(bus, tripType))}
                </div>
            ) : (
                <Card>
                    <CardContent className="p-6 text-center text-muted-foreground">
                        {tripResult.message}
                    </CardContent>
                </Card>
            )}
        </div>
    );

    if (!parsedResults.success) {
        return (
            <div className="container mx-auto p-6">
                <Head title="نتائج البحث" />
                <Card>
                    <CardContent className="p-6 text-center">
                        <h2 className="text-2xl font-bold mb-2">عذراً</h2>
                        <p className="text-muted-foreground">
                            {parsedResults.message || 'لم نتمكن من العثور على رحلات متاحة'}
                        </p>
                    </CardContent>
                </Card>
            </div>
        );
    }

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
        <div className="container mx-auto p-6">
            <Head title="نتائج البحث" />

            {/* ملخص البحث */}
            <Card className="mb-6">
                <CardHeader>
                    <CardTitle>ملخص البحث</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="flex items-center gap-2">
                            <MapPin className="h-4 w-4" />
                            <span className="text-sm">
                                {parsedResults.trip.from_city} ← {parsedResults.trip.to_city}
                            </span>
                        </div>
                        <div className="flex items-center gap-2">
                            <Users className="h-4 w-4" />
                            <span className="text-sm">عدد المسافرين: {parsedResults.passenger_count}</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <Calendar className="h-4 w-4" />
                            <span className="text-sm">نوع الرحلة: {getTripTypeLabel(parsedResults.trip_type)}</span>
                        </div>
                        <div className="text-sm">
                            <span className="font-bold">السعر الإجمالي: </span>
                            <span className="text-lg text-green-600">{parsedResults.total_price} جنيه</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* زر المتابعة للحجز في حالة الذهاب والعودة */}
            {isRoundTrip && (selectedGoBus || selectedReturnBus) && (
                <Card className="mb-6 bg-primary/5">
                    <CardContent className="p-6">
                        <div className="space-y-4">
                            <h3 className="font-semibold text-lg">اختياراتك:</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className={`p-4 rounded-lg border-2 ${selectedGoBus ? 'border-primary bg-primary/10' : 'border-dashed border-gray-300'}`}>
                                    <div className="flex items-center gap-2 mb-2">
                                        <Bus className="h-4 w-4" />
                                        <span className="font-medium">رحلة الذهاب</span>
                                    </div>
                                    {selectedGoBus ? (
                                        <div className="text-sm">
                                            <div className="flex items-center gap-2">
                                                <Check className="h-4 w-4 text-primary" />
                                                <span>{selectedGoBus.bus_name}</span>
                                            </div>
                                            <div className="text-muted-foreground mt-1">
                                                {selectedGoBus.plate_number}
                                            </div>
                                        </div>
                                    ) : (
                                        <p className="text-sm text-muted-foreground">لم يتم الاختيار بعد</p>
                                    )}
                                </div>
                                <div className={`p-4 rounded-lg border-2 ${selectedReturnBus ? 'border-primary bg-primary/10' : 'border-dashed border-gray-300'}`}>
                                    <div className="flex items-center gap-2 mb-2">
                                        <Bus className="h-4 w-4" />
                                        <span className="font-medium">رحلة العودة</span>
                                    </div>
                                    {selectedReturnBus ? (
                                        <div className="text-sm">
                                            <div className="flex items-center gap-2">
                                                <Check className="h-4 w-4 text-primary" />
                                                <span>{selectedReturnBus.bus_name}</span>
                                            </div>
                                            <div className="text-muted-foreground mt-1">
                                                {selectedReturnBus.plate_number}
                                            </div>
                                        </div>
                                    ) : (
                                        <p className="text-sm text-muted-foreground">لم يتم الاختيار بعد</p>
                                    )}
                                </div>
                            </div>
                            <Button
                                className="w-full"
                                size="lg"
                                disabled={!selectedGoBus || !selectedReturnBus}
                                onClick={handleRoundTripBooking}
                            >
                                متابعة الحجز ({selectedGoBus && selectedReturnBus ? 'اختيار المقاعد' : 'اختر الأوتوبيسات أولاً'})
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* نتائج رحلة الذهاب */}
            {parsedResults.results.go_trip &&
                renderTripSection(parsedResults.results.go_trip, 'رحلة الذهاب', 'go')}

            {/* نتائج رحلة العودة */}
            {parsedResults.results.return_trip &&
                renderTripSection(parsedResults.results.return_trip, 'رحلة العودة', 'return')}
        </div>
        </AppLayout>
    );
};

export default TripResults;
