import { Head, router, usePage } from '@inertiajs/react';
import axios from 'axios';
import { Check, Bus as BusIcon, ArrowRight } from 'lucide-react';
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

interface TripInfo {
    schedule_trip_id: number;
    bus_id: number;
    bus_name: string;
    type: 'go_way' | 'return_way';
    price: number;
    from_city: string;
    to_city: string;
    date: string;
}

interface TripData {
    trip_type: 'round_trip';
    passenger_count: number;
    go_trip: TripInfo;
    return_trip: TripInfo;
}

interface Props {
    tripData: string;
}

const RoundTripSeatSelection = ({ tripData }: Props) => {
    const parsedTripData: TripData = JSON.parse(tripData);

    // State for go trip
    const [goSeatsData, setGoSeatsData] = useState<SeatsData | null>(null);
    const [selectedGoSeats, setSelectedGoSeats] = useState<number[]>([]);

    // State for return trip
    const [returnSeatsData, setReturnSeatsData] = useState<SeatsData | null>(null);
    const [selectedReturnSeats, setSelectedReturnSeats] = useState<number[]>([]);

    const [loading, setLoading] = useState(true);
    const [customerName, setCustomerName] = useState('');
    const [phoneNumber, setPhoneNumber] = useState('');
    const [processing, setProcessing] = useState(false);
    const { auth } = usePage().props;
    const userRole = auth.user_role;
    const isSuperAdmin = userRole === 'super_admin';
    const [goTicketPrice, setGoTicketPrice] = useState<number>(parsedTripData.go_trip.price);
    const [returnTicketPrice, setReturnTicketPrice] = useState<number>(parsedTripData.return_trip.price);

    useEffect(() => {
        fetchAllSeats();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const fetchAllSeats = async () => {
        try {
            setLoading(true);

            // Fetch seats for go trip
            const goResponse = await axios.post('/bookings/bus-seats', {
                schedule_trip_id: parsedTripData.go_trip.schedule_trip_id,
                bus_id: parsedTripData.go_trip.bus_id,
                from_city: parsedTripData.go_trip.from_city,
                to_city: parsedTripData.go_trip.to_city,
            });

            if (goResponse.data.success) {
                setGoSeatsData(goResponse.data);
            }

            // Fetch seats for return trip
            const returnResponse = await axios.post('/bookings/bus-seats', {
                schedule_trip_id: parsedTripData.return_trip.schedule_trip_id,
                bus_id: parsedTripData.return_trip.bus_id,
                from_city: parsedTripData.return_trip.from_city,
                to_city: parsedTripData.return_trip.to_city,
            });

            if (returnResponse.data.success) {
                setReturnSeatsData(returnResponse.data);
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

    const toggleGoSeat = (seatNumber: number) => {
        if (selectedGoSeats.includes(seatNumber)) {
            setSelectedGoSeats(selectedGoSeats.filter((s) => s !== seatNumber));
        } else {
            if (selectedGoSeats.length >= parsedTripData.passenger_count) {
                toast({
                    variant: 'destructive',
                    title: 'تنبيه',
                    description: `لا يمكن اختيار أكثر من ${parsedTripData.passenger_count} مقاعد`,
                });
                return;
            }
            setSelectedGoSeats([...selectedGoSeats, seatNumber]);
        }
    };

    const toggleReturnSeat = (seatNumber: number) => {
        if (selectedReturnSeats.includes(seatNumber)) {
            setSelectedReturnSeats(selectedReturnSeats.filter((s) => s !== seatNumber));
        } else {
            if (selectedReturnSeats.length >= parsedTripData.passenger_count) {
                toast({
                    variant: 'destructive',
                    title: 'تنبيه',
                    description: `لا يمكن اختيار أكثر من ${parsedTripData.passenger_count} مقاعد`,
                });
                return;
            }
            setSelectedReturnSeats([...selectedReturnSeats, seatNumber]);
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

        if (selectedGoSeats.length === 0 || selectedReturnSeats.length === 0) {
            toast({
                variant: 'destructive',
                title: 'خطأ',
                description: 'يجب اختيار مقاعد للذهاب والعودة',
            });
            return;
        }

        if (selectedGoSeats.length !== parsedTripData.passenger_count ||
            selectedReturnSeats.length !== parsedTripData.passenger_count) {
            toast({
                variant: 'destructive',
                title: 'خطأ',
                description: `يجب اختيار ${parsedTripData.passenger_count} مقاعد بالضبط لكل رحلة`,
            });
            return;
        }

        try {
            setProcessing(true);

            // Check availability for both trips
            const checkGoResponse = await axios.post('/bookings/check-seats', {
                schedule_trip_id: parsedTripData.go_trip.schedule_trip_id,
                bus_id: parsedTripData.go_trip.bus_id,
                seat_numbers: selectedGoSeats,
                from_city: parsedTripData.go_trip.from_city,
                to_city: parsedTripData.go_trip.to_city,
            });

            if (!checkGoResponse.data.available) {
                toast({
                    variant: 'destructive',
                    title: 'خطأ في رحلة الذهاب',
                    description: checkGoResponse.data.message,
                });
                await fetchAllSeats();
                setSelectedGoSeats([]);
                setSelectedReturnSeats([]);
                return;
            }

            const checkReturnResponse = await axios.post('/bookings/check-seats', {
                schedule_trip_id: parsedTripData.return_trip.schedule_trip_id,
                bus_id: parsedTripData.return_trip.bus_id,
                seat_numbers: selectedReturnSeats,
                from_city: parsedTripData.return_trip.from_city,
                to_city: parsedTripData.return_trip.to_city,
            });

            if (!checkReturnResponse.data.available) {
                toast({
                    variant: 'destructive',
                    title: 'خطأ في رحلة العودة',
                    description: checkReturnResponse.data.message,
                });
                await fetchAllSeats();
                setSelectedGoSeats([]);
                setSelectedReturnSeats([]);
                return;
            }

            // Create booking with both trips
            const totalPrice =
                (goTicketPrice * selectedGoSeats.length) +
                (returnTicketPrice * selectedReturnSeats.length);

            const bookingData = {
                customer_name: customerName,
                phone_number: phoneNumber,
                subtotal_price: totalPrice,
                items: [
                    {
                        schedule_trip_id: parsedTripData.go_trip.schedule_trip_id,
                        bus_id: parsedTripData.go_trip.bus_id,
                        type: parsedTripData.go_trip.type,
                        seat_numbers: selectedGoSeats,
                        price: goTicketPrice * selectedGoSeats.length,
                        from_city: parsedTripData.go_trip.from_city,
                        to_city: parsedTripData.go_trip.to_city,
                    },
                    {
                        schedule_trip_id: parsedTripData.return_trip.schedule_trip_id,
                        bus_id: parsedTripData.return_trip.bus_id,
                        type: parsedTripData.return_trip.type,
                        seat_numbers: selectedReturnSeats,
                        price: returnTicketPrice * selectedReturnSeats.length,
                        from_city: parsedTripData.return_trip.from_city,
                        to_city: parsedTripData.return_trip.to_city,
                    },
                ],
            };

            const response = await axios.post('/bookings/create', bookingData);

            if (response.data.success) {
                toast({
                    title: 'نجح الحجز',
                    description: 'تم إنشاء حجز الذهاب والعودة بنجاح',
                });

                // افتح صفحة التذكرة للطباعة في نافذة جديدة ثم عُد إلى لوحة التحكم
                const bookingId = response.data.booking?.id;
                if (bookingId) {
                    window.open(`/bookings/${bookingId}/print`, '_blank');
                }

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

    const getSeatColor = (seat: Seat, selectedSeats: number[]) => {
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

    if (!goSeatsData || !returnSeatsData) {
        return (
            <div className="container mx-auto p-6">
                <Head title="اختيار المقاعد" />
                <div className="text-center text-red-500">فشل تحميل بيانات المقاعد</div>
            </div>
        );
    }

    const totalPrice =
        (selectedGoSeats.length * goTicketPrice) +
        (selectedReturnSeats.length * returnTicketPrice);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ];

    const renderSeatGrid = (
        seatsData: SeatsData,
        selectedSeats: number[],
        toggleSeat: (seat: number) => void,
        title: string,
        tripInfo: TripInfo
    ) => (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <BusIcon className="h-5 w-5" />
                    {title}
                </CardTitle>
                <CardDescription>
                    <div className="space-y-1">
                        <div>{seatsData.bus.name} - {seatsData.bus.plate_number}</div>
                        <div className="text-xs">
                            {tripInfo.from_city} <ArrowRight className="inline h-3 w-3" /> {tripInfo.to_city}
                        </div>
                        <div className="text-xs">{tripInfo.date}</div>
                    </div>
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="mb-4">
                    <div className="flex gap-4 justify-center mb-4">
                        <div className="flex items-center gap-2">
                            <div className="w-6 h-6 bg-gray-200 rounded"></div>
                            <span className="text-xs">متاح</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-6 h-6 bg-green-500 rounded"></div>
                            <span className="text-xs">مختار</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-6 h-6 bg-red-500 rounded"></div>
                            <span className="text-xs">محجوز</span>
                        </div>
                    </div>
                </div>

                {/* عرض المقاعد */}
                <div className="grid grid-cols-4 gap-2">
                    {seatsData.seats.map((seat) => (
                        <button
                            key={seat.number}
                            onClick={() => !seat.is_reserved && toggleSeat(seat.number)}
                            disabled={seat.is_reserved}
                            className={`
                                relative h-12 rounded-lg font-semibold text-xs
                                transition-all duration-200
                                ${getSeatColor(seat, selectedSeats)}
                                disabled:opacity-50
                            `}
                        >
                            {seat.number}
                            {selectedSeats.includes(seat.number) && (
                                <Check className="absolute top-0.5 right-0.5 h-3 w-3" />
                            )}
                        </button>
                    ))}
                </div>

                <div className="mt-4 flex flex-wrap gap-2">
                    <Badge variant="outline">متاح: {seatsData.available_count}</Badge>
                    <Badge variant="outline">محجوز: {seatsData.reserved_count}</Badge>
                    <Badge variant="default">مختار: {selectedSeats.length}</Badge>
                </div>
            </CardContent>
        </Card>
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <div className="container mx-auto p-6">
                <Head title="اختيار مقاعد الذهاب والعودة" />

                <div className="mb-6">
                    <h1 className="text-2xl font-bold mb-2">حجز رحلة الذهاب والعودة</h1>
                    <p className="text-muted-foreground">
                        اختر {parsedTripData.passenger_count} مقاعد لكل رحلة
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* معلومات الحجز */}
                    <div className="lg:order-2">
                        <Card className="sticky top-6">
                            <CardHeader>
                                <CardTitle>معلومات الحجز</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {/* المقاعد المختارة - الذهاب */}
                                <div>
                                    <Label className="flex items-center gap-2">
                                        <ArrowRight className="h-4 w-4" />
                                        مقاعد الذهاب ({selectedGoSeats.length})
                                    </Label>
                                    <div className="flex flex-wrap gap-1 mt-2">
                                        {selectedGoSeats.length > 0 ? (
                                            selectedGoSeats.sort((a, b) => a - b).map((seat) => (
                                                <Badge key={seat} variant="default" className="text-xs">
                                                    {seat}
                                                </Badge>
                                            ))
                                        ) : (
                                            <span className="text-xs text-gray-500">لم يتم اختيار مقاعد</span>
                                        )}
                                    </div>
                                </div>

                                {/* المقاعد المختارة - العودة */}
                                <div>
                                    <Label className="flex items-center gap-2">
                                        <ArrowRight className="h-4 w-4 rotate-180" />
                                        مقاعد العودة ({selectedReturnSeats.length})
                                    </Label>
                                    <div className="flex flex-wrap gap-1 mt-2">
                                        {selectedReturnSeats.length > 0 ? (
                                            selectedReturnSeats.sort((a, b) => a - b).map((seat) => (
                                                <Badge key={seat} variant="default" className="text-xs">
                                                    {seat}
                                                </Badge>
                                            ))
                                        ) : (
                                            <span className="text-xs text-gray-500">لم يتم اختيار مقاعد</span>
                                        )}
                                    </div>
                                </div>
                                {/* بيانات العميل */}
                                <div className="space-y-3 pt-4 border-t">
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
                                        <div className="grid grid-cols-1 gap-2">
                                            <div>
                                                <Label htmlFor="go_ticket_price">سعر مقعد الذهاب (مشرف)</Label>
                                                <Input
                                                    id="go_ticket_price"
                                                    type="number"
                                                    value={goTicketPrice}
                                                    onChange={(e) => setGoTicketPrice(Number(e.target.value))}
                                                    placeholder="أدخل سعر مقعد الذهاب"
                                                />
                                            </div>
                                            <div>
                                                <Label htmlFor="return_ticket_price">سعر مقعد العودة (مشرف)</Label>
                                                <Input
                                                    id="return_ticket_price"
                                                    type="number"
                                                    value={returnTicketPrice}
                                                    onChange={(e) => setReturnTicketPrice(Number(e.target.value))}
                                                    placeholder="أدخل سعر مقعد العودة"
                                                />
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* السعر الإجمالي */}
                                <div className="border-t pt-4">
                                    <div className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <span>رحلة الذهاب:</span>
                                            <span>{selectedGoSeats.length * goTicketPrice} جنيه</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>رحلة العودة:</span>
                                            <span>{selectedReturnSeats.length * returnTicketPrice} جنيه</span>
                                        </div>
                                        <div className="flex justify-between items-center text-lg font-bold border-t pt-2">
                                            <span>الإجمالي:</span>
                                            <span>{totalPrice} جنيه</span>
                                        </div>
                                    </div>
                                </div>

                                {/* زر الحجز */}
                                <Button
                                    onClick={handleBooking}
                                    disabled={
                                        processing ||
                                        selectedGoSeats.length === 0 ||
                                        selectedReturnSeats.length === 0
                                    }
                                    className="w-full"
                                    size="lg"
                                >
                                    {processing ? 'جاري الحجز...' : 'تأكيد الحجز'}
                                </Button>
                            </CardContent>
                        </Card>
                    </div>

                    {/* مقاعد الذهاب */}
                    <div className="lg:order-1">
                        {renderSeatGrid(
                            goSeatsData,
                            selectedGoSeats,
                            toggleGoSeat,
                            'رحلة الذهاب',
                            parsedTripData.go_trip
                        )}
                    </div>

                    {/* مقاعد العودة */}
                    <div className="lg:order-3">
                        {renderSeatGrid(
                            returnSeatsData,
                            selectedReturnSeats,
                            toggleReturnSeat,
                            'رحلة العودة',
                            parsedTripData.return_trip
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default RoundTripSeatSelection;
