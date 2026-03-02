import { router } from '@inertiajs/react';
import axios from 'axios';
import { format, startOfDay } from 'date-fns';
import { Minus, Plus } from 'lucide-react';
import React from 'react';

import { Button } from '@/components/ui/button';
import { DatePickerSimple } from '@/components/ui/DatePickerSimple';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { toast } from '@/hooks/use-toast';
// import { toast } from '@/hooks/use-toast';

interface props {
    cities: {
        id: number;
        name: string;
    }[];
}

const TripForm = ({ cities }: props) => {
    const [citytwo, setCitytwo] = React.useState<
        { id: number; name: string }[]
    >([]);
    const [fromCity, setFromCity] = React.useState<string>('');
    const [toCity, setToCity] = React.useState<string>('');
    const [tripDate, setTripDate] = React.useState<Date | undefined>(undefined);
    const [returnDate, setReturnDate] = React.useState<Date | undefined>(undefined);
    const [tripType, setTripType] = React.useState<string>('go');
    const [passengerCount, setPassengerCount] = React.useState<number>(1);
    const [processing, setProcessing] = React.useState<boolean>(false);

    const onSelectChange = async (value: string) => {
        setFromCity(value);
        try {
            const response = await axios.post('/trips', { id: value });
            setCitytwo(response.data);
            
        } catch (error) {
            console.error('Error fetching trips:', error);
        }
    };

    const incrementPassengers = () => {
        setPassengerCount((prev) => Math.min(prev + 1, 8)); // Max 8 passengers
    };

    const decrementPassengers = () => {
        setPassengerCount((prev) => Math.max(prev - 1, 1)); // Min 1 passenger
    };

    const handleDateChange = (date: Date | undefined) => {
        if (date) {
            const today = startOfDay(new Date());
            const selectedDate = startOfDay(date);

            if (selectedDate < today) {
                alert('لا يمكن اختيار تاريخ في الماضي. يرجى اختيار تاريخ اليوم أو تاريخ مستقبلي.');
                return;
            }
        }
        setTripDate(date);
        // Reset return date if it's before the new go date
        if (date && returnDate && startOfDay(returnDate) < startOfDay(date)) {
            setReturnDate(undefined);
        }
    };

    const handleReturnDateChange = (date: Date | undefined) => {
        if (date && tripDate) {
            const goDate = startOfDay(tripDate);
            const selectedReturnDate = startOfDay(date);

            if (selectedReturnDate < goDate) {
                alert('يجب أن يكون تاريخ العودة بعد تاريخ الذهاب.');
                return;
            }
        }
        setReturnDate(date);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);

        try {
            const formData = {
                from_city: fromCity,
                to_city: toCity,
                trip_date: tripDate ? format(tripDate, 'yyyy-MM-dd') : '',
                trip_type: tripType,
                passenger_count: passengerCount,
                return_date: (tripType === 'goreturn' && returnDate)
                    ? format(returnDate, 'yyyy-MM-dd')
                    : null,
            };

            const response = await axios.post('/search-trips', formData);

            if (response.data.success) {
                // توجيه المستخدم إلى صفحة النتائج مع البيانات
                router.visit('/trip-results', {
                    method: 'get',
                    data: {
                        searchData: JSON.stringify(formData),
                        results: JSON.stringify(response.data),
                    },
                });
            } else {
                toast({
                    variant: 'destructive',
                    title: 'خطأ',
                    description: response.data.message || 'حدث خطأ أثناء البحث',
                });
            }
        } catch (error: any) {
            console.error('Error searching trips:', error);

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
                    description: 'حدث خطأ أثناء البحث عن الرحلات',
                });
            }
        } finally {
            setProcessing(false);
        }
    };

    return (
        <div className="w-full max-w-4xl mx-auto p-6">
            <form onSubmit={handleSubmit} className="space-y-6">
                <div className="flex flex-wrap items-center justify-center gap-4">
                    {/* From City */}
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="from-city">السفر من</Label>
                        <Select onValueChange={onSelectChange} value={fromCity}>
                            <SelectTrigger className="w-45" id="from-city">
                                <SelectValue placeholder="اختر المدينة" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    {cities.map((city) => (
                                        <SelectItem
                                            key={city.id}
                                            value={city.id.toString()}
                                        >
                                            {city.name}
                                        </SelectItem>
                                    ))}
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    {/* To City */}
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="to-city">السفر إلى</Label>
                        <Select
                            disabled={citytwo.length === 0}
                            onValueChange={setToCity}
                            value={toCity}
                        >
                            <SelectTrigger className="w-45" id="to-city">
                                <SelectValue placeholder="اختر المدينة" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    {citytwo.map((city) => (
                                        <SelectItem
                                            key={city.id}
                                            value={city.id.toString()}
                                        >
                                            {city.name}
                                        </SelectItem>
                                    ))}
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Date Picker */}
                    <div className="flex flex-col gap-2">
                        <Label>تاريخ السفر</Label>
                        <DatePickerSimple
                            date={tripDate}
                            onDateChange={handleDateChange}
                        />
                    </div>
                </div>

                {/* Trip Type Radio Group */}
                <div className="flex flex-col gap-2 items-center">
                    <Label>نوع الرحلة</Label>
                    <ToggleGroup
                        type="single"
                        value={tripType}
                        onValueChange={(value) => value && setTripType(value)}
                        className="justify-center"
                    >
                        <ToggleGroupItem value="go" aria-label="ذهاب فقط">
                            ذهاب
                        </ToggleGroupItem>
                        <ToggleGroupItem value="return" aria-label="عودة فقط">
                            عودة
                        </ToggleGroupItem>
                        <ToggleGroupItem value="goreturn" aria-label="ذهاب وعودة">
                            ذهاب وعودة
                        </ToggleGroupItem>
                    </ToggleGroup>
                </div>

                {/* Return Date Picker - Show only for round trip */}
                {tripType === 'goreturn' && (
                    <div className="flex flex-col gap-2 items-center">
                        <Label>تاريخ العودة</Label>
                        <DatePickerSimple
                            date={returnDate}
                            onDateChange={handleReturnDateChange}
                        />
                    </div>
                )}

                {/* Passenger Counter */}
                <div className="flex flex-col gap-2 items-center">
                    <Label>عدد المسافرين</Label>
                    <div className="flex items-center gap-4">
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={decrementPassengers}
                            disabled={passengerCount <= 1}
                        >
                            <Minus className="h-4 w-4" />
                        </Button>
                        <span className="text-2xl font-semibold w-16 text-center">
                            {passengerCount}
                        </span>
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={incrementPassengers}
                            disabled={passengerCount >= 8}
                        >
                            <Plus className="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                {/* Submit Button */}
                <Button
                    type="submit"
                    className="w-full"
                    disabled={
                        processing ||
                        !fromCity ||
                        !toCity ||
                        !tripDate ||
                        (tripType === 'goreturn' && !returnDate)
                    }
                >
                    بحث عن رحلات
                </Button>
            </form>
        </div>
    );
};

export default TripForm;
