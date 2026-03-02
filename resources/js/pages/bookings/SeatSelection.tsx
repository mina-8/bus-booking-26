import { Head, router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { Check } from 'lucide-react';
import React, { useEffect, useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types/navigation';

interface Seat {
    number: number;
    is_reserved: boolean;
}

interface BusInfo {
    id: number;
    name: string;
    plate_number: string;
    total_seats: number;
}

interface SeatsData {
    success: boolean;
    bus: BusInfo;
    seats: Seat[];
    reserved_count: number;
    available_count: number;
}

interface TripData {
    schedule_trip_id: number;
    bus_id: number;
    type: 'go_way' | 'return_way';
    price: number;
    from_city: string;
    to_city: string;
    date: string;
    passenger_count: number;
}

interface Props {
    scheduleTripId: string;
    busId: string;
    tripData: string;
}

const SeatSelection = ({ scheduleTripId, busId, tripData }: Props) => {
    const parsedTripData: TripData = JSON.parse(tripData);
    const [seatsData, setSeatsData] = useState<SeatsData | null>(null);
    const [selectedSeats, setSelectedSeats] = useState<number[]>([]);
    const [loading, setLoading] = useState(true);
    const [customerName, setCustomerName] = useState('');
    const [phoneNumber, setPhoneNumber] = useState('');
    const [processing, setProcessing] = useState(false);
    const { auth } = usePage().props;
    const userRole = auth.user_role;
    const isSuperAdmin = userRole === 'super_admin';
    const [ticketPrice, setTicketPrice] = useState<number>(parsedTripData.price);

    useEffect(() => {
        fetchSeats();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const fetchSeats = async () => {
        try {
            setLoading(true);
            const response = await axios.post('/bookings/bus-seats', {
                schedule_trip_id: scheduleTripId,
                bus_id: busId,
                from_city: parsedTripData.from_city,
                to_city: parsedTripData.to_city,
            });

            if (response.data.success) {
                setSeatsData(response.data);
            } else {
                toast({
                    variant: 'destructive',
                    title: 'خطأ',
                    description: response.data.message,
                });
            }
        } catch (error) {
            console.error('Error fetching seats:', error);
            toast({
                variant: 'destructive',
                title: 'خطأ',
                description: 'حدث خطأ أثناء تحميل المقاعد',
            });
        } finally {
            setLoading(false);
        }
    };

    const toggleSeat = (seatNumber: number) => {
        if (selectedSeats.includes(seatNumber)) {
            setSelectedSeats(selectedSeats.filter((s) => s !== seatNumber));
        } else {
            // التحقق من عدم تجاوز عدد المسافرين
            if (selectedSeats.length >= parsedTripData.passenger_count) {
                toast({
                    variant: 'destructive',
                    title: 'تنبيه',
                    description: `لا يمكن اختيار أكثر من ${parsedTripData.passenger_count} مقاعد`,
                });
                return;
            }
            setSelectedSeats([...selectedSeats, seatNumber]);
        }
    };

    const handleBooking = async () => {
        if (!customerName || !phoneNumber) {
            toast({
                variant: 'destructive',
                title: 'خطأ',
                description: 'يجب إدخال الاسم ورقم الهاتف',
            });
            return;
        }

        if (selectedSeats.length === 0) {
            toast({
                variant: 'destructive',
                title: 'خطأ',
                description: 'يجب اختيار مقعد واحد على الأقل',
            });
            return;
        }

        if (selectedSeats.length !== parsedTripData.passenger_count) {
            toast({
                variant: 'destructive',
                title: 'خطأ',
                description: `يجب اختيار ${parsedTripData.passenger_count} مقاعد بالضبط`,
            });
            return;
        }

        try {
            setProcessing(true);

            // التحقق من توفر المقاعد أولاً
            const checkResponse = await axios.post('/bookings/check-seats', {
                schedule_trip_id: scheduleTripId,
                bus_id: busId,
                seat_numbers: selectedSeats,
                from_city: parsedTripData.from_city,
                to_city: parsedTripData.to_city,
            });

            if (!checkResponse.data.available) {
                toast({
                    variant: 'destructive',
                    title: 'خطأ',
                    description: checkResponse.data.message,
                });
                // إعادة تحميل المقاعد
                await fetchSeats();
                setSelectedSeats([]);
                return;
            }

            // إنشاء الحجز
            const bookingData = {
                customer_name: customerName,
                phone_number: phoneNumber,
                subtotal_price: ticketPrice * selectedSeats.length,
                items: [
                    {
                        schedule_trip_id: parseInt(scheduleTripId),
                        bus_id: parseInt(busId),
                        type: parsedTripData.type,
                        seat_numbers: selectedSeats,
                        price: ticketPrice * selectedSeats.length,
                        from_city: parsedTripData.from_city,
                        to_city: parsedTripData.to_city,
                    },
                ],
            };

            const response = await axios.post('/bookings/create', bookingData);

            if (response.data.success) {
                toast({
                    title: 'نجح الحجز',
                    description: 'تم إنشاء حجزك بنجاح',
                });

                const bookingId = response.data.booking?.id;
                if (bookingId) {
                    window.open(`/bookings/${bookingId}/print`, '_blank');
                }

                // التوجيه بعد ظهور النافذة
                setTimeout(() => {
                    router.visit('/dashboard');
                }, 2000);
            } else {
                toast({
                    variant: 'destructive',
                    title: 'خطأ',
                    description: response.data.message,
                });
            }
        } catch (error: any) {
            console.error('Error creating booking:', error);

            if (error.response?.data?.errors) {
                const errorMessages = Object.values(error.response.data.errors).flat();
                toast({
                    variant: 'destructive',
                    title: 'خطأ في البيانات',
                    description: errorMessages.join(', '),
                });
            } else {
                toast({
                    variant: 'destructive',
                    title: 'خطأ',
                    description: 'حدث خطأ أثناء إنشاء الحجز',
                });
            }
        } finally {
            setProcessing(false);
        }
    };

    const getSeatColor = (seat: Seat) => {
        if (seat.is_reserved) {
            return 'bg-red-500 cursor-not-allowed';
        }
        if (selectedSeats.includes(seat.number)) {
            return 'bg-green-500 text-white';
        }
        return 'bg-gray-200 hover:bg-blue-200 cursor-pointer';
    };

    if (loading) {
        return (
            <div className="container mx-auto p-6">
                <Head title="اختيار المقاعد" />
                <div className="text-center">جاري تحميل المقاعد...</div>
            </div>
        );
    }

    if (!seatsData) {
        return (
            <div className="container mx-auto p-6">
                <Head title="اختيار المقاعد" />
                <div className="text-center text-red-500">فشل تحميل بيانات المقاعد</div>
            </div>
        );
    }

    const totalPrice = selectedSeats.length * ticketPrice;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
        <div className="container mx-auto p-6">
            <Head title="اختيار المقاعد" />

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* معلومات السيارة والمقاعد */}
                <div className="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>اختر مقاعدك</CardTitle>
                            <CardDescription>
                                {seatsData.bus.name} - {seatsData.bus.plate_number}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="mb-6">
                                <div className="flex gap-4 justify-center mb-4">
                                    <div className="flex items-center gap-2">
                                        <div className="w-8 h-8 bg-gray-200 rounded"></div>
                                        <span className="text-sm">متاح</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <div className="w-8 h-8 bg-green-500 rounded"></div>
                                        <span className="text-sm">مختار</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <div className="w-8 h-8 bg-red-500 rounded"></div>
                                        <span className="text-sm">محجوز</span>
                                    </div>
                                </div>
                            </div>

                            {/* عرض المقاعد */}
                            <div className="grid grid-cols-4 gap-3">
                                {seatsData.seats.map((seat) => (
                                    <button
                                        key={seat.number}
                                        onClick={() => !seat.is_reserved && toggleSeat(seat.number)}
                                        disabled={seat.is_reserved}
                                        className={`
                                            relative h-16 rounded-lg font-semibold text-sm
                                            transition-all duration-200
                                            ${getSeatColor(seat)}
                                            disabled:opacity-50
                                        `}
                                    >
                                        {seat.number}
                                        {selectedSeats.includes(seat.number) && (
                                            <Check className="absolute top-1 right-1 h-4 w-4" />
                                        )}
                                    </button>
                                ))}
                            </div>

                            <div className="mt-6 flex justify-between text-sm">
                                <span>المقاعد المتاحة: {seatsData.available_count}</span>
                                <span>المقاعد المحجوزة: {seatsData.reserved_count}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* معلومات الحجز */}
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle>معلومات الحجز</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {/* معلومات الرحلة */}
                            <div className="space-y-2">
                                <div className="text-sm">
                                    <span className="font-semibold">من:</span> {parsedTripData.from_city}
                                </div>
                                <div className="text-sm">
                                    <span className="font-semibold">إلى:</span> {parsedTripData.to_city}
                                </div>
                                <div className="text-sm">
                                    <span className="font-semibold">التاريخ:</span> {parsedTripData.date}
                                </div>
                                <div className="text-sm">
                                    <span className="font-semibold">عدد المسافرين:</span> {parsedTripData.passenger_count}
                                </div>
                                <div className="text-sm">
                                    <span className="font-semibold">سعر المقعد:</span> {ticketPrice} جنيه
                                </div>
                            </div>

                            {/* المقاعد المختارة */}
                            <div>
                                <Label>المقاعد المختارة ({selectedSeats.length})</Label>
                                <div className="flex flex-wrap gap-2 mt-2">
                                    {selectedSeats.length > 0 ? (
                                        selectedSeats.sort((a, b) => a - b).map((seat) => (
                                            <Badge key={seat} variant="default">
                                                {seat}
                                            </Badge>
                                        ))
                                    ) : (
                                        <span className="text-sm text-gray-500">لم يتم اختيار مقاعد</span>
                                    )}
                                </div>
                            </div>

                            {/* بيانات العميل */}
                            <div className="space-y-3">
                                <div>
                                    <Label htmlFor="customer_name">الاسم</Label>
                                    <Input
                                        id="customer_name"
                                        value={customerName}
                                        onChange={(e) => setCustomerName(e.target.value)}
                                        placeholder="أدخل اسمك"
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="phone_number">رقم الهاتف</Label>
                                    <Input
                                        id="phone_number"
                                        value={phoneNumber}
                                        onChange={(e) => setPhoneNumber(e.target.value)}
                                        placeholder="أدخل رقم هاتفك"
                                    />
                                </div>
                                {isSuperAdmin && (
                                    <div>
                                        <Label htmlFor="ticket_price">سعر المقعد (مشرف)</Label>
                                        <Input
                                            id="ticket_price"
                                            type="number"
                                            value={ticketPrice}
                                            onChange={(e) => setTicketPrice(Number(e.target.value))}
                                            placeholder="أدخل سعر المقعد"
                                        />
                                    </div>
                                )}
                            </div>

                            {/* السعر الإجمالي */}
                            <div className="border-t pt-4">
                                <div className="flex justify-between items-center text-lg font-bold">
                                    <span>الإجمالي:</span>
                                    <span>{totalPrice} جنيه</span>
                                </div>
                            </div>

                            {/* زر الحجز */}
                            <Button
                                onClick={handleBooking}
                                disabled={processing || selectedSeats.length === 0}
                                className="w-full"
                            >
                                {processing ? 'جاري الحجز...' : 'تأكيد الحجز'}
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
        </AppLayout>
    );
};

export default SeatSelection;
