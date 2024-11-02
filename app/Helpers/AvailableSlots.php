<?php

namespace App\Helpers;

class AvailableSlots
{

    public int $timeBlockInterval;
    public int $serviceDuration;
    public $employeCustomDates;
    public \stdClass $weekDaysPeriods;
    public \stdClass $employesBookings;
    public $employes;
    public bool $isFirst = true;
    public bool $second = true;

    public function __construct(int $timeBlockInterval, int $serviceDuration, \stdClass $weekDaysPeriods = new \stdClass(), $employeCustomDates, $employes, $employesBookings)
    {
        $this->timeBlockInterval = $timeBlockInterval;
        $json = json_encode($weekDaysPeriods, JSON_PRETTY_PRINT);
        $this->weekDaysPeriods = $weekDaysPeriods;
        $this->serviceDuration = $serviceDuration;
        $this->employeCustomDates = $employeCustomDates;
        $this->employes = $employes;
        $this->employesBookings = $employesBookings;
    }

    public function generateCalendar($year, $month)
    {
        // Vytvoření počátečního data
        $startDate = new \DateTime("$year-$month-01");
        // Posun na pondělí před prvním dnem měsíce
        $startDate->modify('last monday');

        // Nastavení koncového data na poslední den měsíce plus 1 rok minus 1 den
        $endDate = (clone $startDate)->modify('+1 year - 1 day');

        // Pole pro týdny
        $weeks = new \stdClass();
        $week = [];
        $currentWeekNumber = (int)$startDate->format('W'); // ISO číslo týdne

        // Procházení každého dne od počátečního do koncového data
        while ($startDate <= $endDate) {
            // Přidání dnů do týdne
            for ($day = 0; $day < 7; $day++) {
                // Vytvoření data pro aktuální den
                $currentDate = (clone $startDate)->modify("+$day days"); // Posun na aktuální den

                // Přidání dne do týdne
                $week[$currentDate->format('Y-m-d')] = $this->getDateSlots($currentDate->format('Y-m-d'));
            }

            // Přidání týdne do pole týdnů
            if (!isset($weeks->{$currentWeekNumber})) {
                $weeks->{$currentWeekNumber} = $week;
            }
            $week = []; // Resetování týdne

            // Posun na další týden
            $startDate->modify('+1 week');
            $currentWeekNumber = (int)$startDate->format('W'); // Aktualizace čísla týdne
        }

        return $weeks;
    }

    private function isTimeAvaible($date, $minutes): array
    {
        $avaibleEmployes = [];
        $this->employes->each(function ($employeID) use (&$avaibleEmployes) {
            $avaibleEmployes[$employeID] = true;
        });


        // Zaměstnanec má volno
        $filteredCustomDates = array_filter($this->employeCustomDates, function ($item) use ($date) {
            $datetime = new \DateTime($date);
            return (($item['custom_date'] == null) && $datetime->format('N') == $item['week_day']) || $item['custom_date'] == $date;
        });
        foreach ($filteredCustomDates as $item) {
            $start = $item['start_time'];
            $end = $item['end_time'] != 0 ? $item['end_time'] : 1440;
            if ($start == 0 && $end == 1440 || $minutes < $start || $minutes > $start && (($minutes + $this->serviceDuration) > $end)) {
                $avaibleEmployes[$item['agent_id']] = false;
            }
        }

        
        foreach (array_keys(array_filter($avaibleEmployes)) as $employe) {
            // Získání všech rezervací pro zaměstnance na dané datum
            $thisDateEmployeBookings = $this->employesBookings->{$employe}->{$date} ?? [];

            // Procházení všech rezervací
            foreach ($thisDateEmployeBookings as $booking) {
                // Výpočet času, kdy nová služba začne a skončí
                $newServiceStart = $minutes;
                $newServiceEnd = $minutes + $this->serviceDuration;

                // Existující rezervace zaměstnance
                $bookingStart = $booking['start_time'];
                $bookingEnd = $booking['end_time'];

               

                // Kontrola překrytí intervalu (kdykoliv nový interval zasahuje do rezervace)
                $isOverlapping = !(
                    $newServiceEnd <= $bookingStart ||  // Nová služba končí před začátkem rezervace
                    $newServiceStart >= $bookingEnd     // Nová služba začíná po konci rezervace
                );

                // Pokud existuje překrytí, znamená to, že zaměstnanec není dostupný
                if ($isOverlapping) {
                    $avaibleEmployes[$employe] = false;
                    break; // Můžeme zastavit kontrolu, zaměstnanec již není dostupný
                }
            }
        }

        return array_keys(array_filter($avaibleEmployes));
    }

    private function getDateSlots($date)
    {
        $dayOfWeek = date('N', strtotime($date));
        $startTime = $this->weekDaysPeriods->{$dayOfWeek}['start_time']; // start_time v minutách
        $endTime = $this->weekDaysPeriods->{$dayOfWeek}['end_time'];     // end_time v minutách

        // Kontrola platnosti časů
        if ($startTime < 0 || $endTime > 1440 || $startTime >= $endTime) {
            return new \stdClass(); // Pokud jsou časy neplatné
        }

        $slots = new \stdClass();

        // Generování slotů
        for ($currentMinutes = $startTime; $currentMinutes <= $endTime - $this->serviceDuration; $currentMinutes += $this->timeBlockInterval) {
            // Formátování času z minut
            $hours = floor($currentMinutes / 60);
            $minutes = $currentMinutes % 60;
            $avaibleEmployes = $this->isTimeAvaible($date, $currentMinutes);
            if (count($avaibleEmployes)) {
                $slots->{$currentMinutes} = [
                    'time' => sprintf("%02d:%02d", $hours, $minutes),
                    'avaibleEmployes' => $avaibleEmployes,
                ];
            }
        }

        return $slots;
    }
}
