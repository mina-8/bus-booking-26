"use client"

import * as React from "react"
import { format, startOfDay } from "date-fns"
import { ar } from "date-fns/locale"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Field, FieldLabel } from "@/components/ui/field"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface DatePickerSimpleProps {
  date?: Date | undefined
  onDateChange?: (date: Date | undefined) => void
}

export function DatePickerSimple({ date, onDateChange }: DatePickerSimpleProps) {
  const [open, setOpen] = React.useState(false)
  const today = startOfDay(new Date())

  const formatDate = (date: Date) => {
    // Format: e.g., "الجمعة، 15 مارس 2024"
    return format(date, "EEEE، d MMMM yyyy", { locale: ar })
  }

  return (
    <Field className="mx-auto w-auto min-w-56">
      {/* <FieldLabel htmlFor="date">Date of birth</FieldLabel> */}
      <Popover open={open} onOpenChange={setOpen}>
        <PopoverTrigger asChild>
          <Button
            variant="outline"
            id="date"
            className="justify-start font-normal"
          >
            {date ? formatDate(date) : "اختر التاريخ"}
          </Button>
        </PopoverTrigger>
        <PopoverContent className="w-auto overflow-hidden p-0" align="start">
          <Calendar
            mode="single"
            selected={date}
            defaultMonth={date}
            captionLayout="dropdown"
            disabled={(date) => date < today}
            onSelect={(selectedDate) => {
              onDateChange?.(selectedDate)
              setOpen(false)
            }}
          />
        </PopoverContent>
      </Popover>
    </Field>
  )
}
