<?php

namespace App\Helpers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Setting;
use App\Models\WorkPeriod;

class AvailableSlots
{

    public int $timeBlockInterval;
    public Service $service;
    public $employeCustomDates = null;
    public \stdClass $weekDaysPeriods;
    public \stdClass | null $employesBookings= null;
    public $employes;
    public bool $isFirst = true;
    public bool $second = true;

    public function __construct(Service $service)
    {
        $employes = $service->agents->pluck('id') ?? [];

        $timeBlockInterval = (int)Setting::where('name', 'timeblock_interval')->pluck('value')->first();

        $this->timeBlockInterval = $timeBlockInterval;
        $this->service = $service;
        $this->employes = $employes;
    }

    private function setEmployesBookings(){
        $employesBookings = new \stdClass();

        $this->employes->each(function ($agent) use (&$employesBookings) {
            $employesBookings->{$agent} = new \stdClass();
        });

        $this->employesBookings = $employesBookings;
    }

    public function generateCalendar($year, $month, int $agentId = null)
    {
        $this->setEmployesBookings();
        
        // Změna dotazu na pracovní období tak, aby zahrnoval celý rok
        $specificDates = WorkPeriod::where(function ($query) use ($month, $year) {
            $query->whereYear('custom_date', $year)
                ->whereMonth('custom_date', '>=', $month)
                ->orWhere(function ($query) use ($month, $year) {
                    $query->whereYear('custom_date', $year + 1)
                        ->whereMonth('custom_date', '<', $month);
                });
        })
            ->orWhereNull('custom_date')
            ->get()->toArray();

        $weekDaysPeriods = new \stdClass();

        // Globální provozní doba
        foreach ($specificDates as $item) {
            if($item['custom_date'] == null && $item['agent_id'] == null && $item['service_id'] == null && $item['location_id'] == null){
                $weekDaysPeriods->{$item['week_day']} = $item;
            }
        }

        $this->weekDaysPeriods = $weekDaysPeriods;

        // TODO: Služba má specifickou provozní dobu
        // $serviceSpecificDates = $specificDates->filter(function ($item) use ($serviceId) {
        //     return $item->service_id == $serviceId;
        // });

        // Zaměstnanec / Zaměstnanci má/mají specifickou provozní dobu
        $employeCustomDates = array_filter($specificDates, function ($item) use ($agentId){
            return ($agentId ? $item['agent_id'] == $agentId : $this->employes->contains($item['agent_id'])) && $item['service_id'] == 0 || $item['service_id'] == $this->service->id;
        });

        $this->employeCustomDates = $employeCustomDates;

        // Změna dotazu tak, aby zahrnoval celý rok
        $bookings = Booking::where(function ($query) use ($month, $year) {
            $query->whereYear('start_date', $year) // Zahrnout celý rok
            ->where(function ($query) use ($month) {
                $query->whereMonth('start_date', '>=', $month)
                    ->orWhereMonth('start_date', '<', $month);
            });
        })
        ->whereIn('agent_id', $this->employes)
        ->get()->toArray();

        foreach ($bookings as $item) {
            // Zkontrolujeme, zda existuje agent a datum v agentsBookings
            if (isset($this->employesBookings->{$item['agent_id']}->{$item['start_date']})) {
                // Pokud existuje, přidáme nový booking
                $this->employesBookings->{$item['agent_id']}->{$item['start_date']}[] = [
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                ];
            } else {
                // Pokud neexistuje, vytvoříme novou položku
                $this->employesBookings->{$item['agent_id']}->{$item['start_date']} = [
                    [
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time'],
                    ]
                ];
            }
        }

          // Vytvoření počátečního data na první den měsíce
        $firstDayOfMonth = new \DateTime("$year-$month-01");
        // Posun na poslední pondělí před nebo v prvním dni měsíce
        $startDate = $firstDayOfMonth->modify('last monday');

        // Pokud je první den měsíce pondělí, vrátí to tento den, jinak se vrátí poslední pondělí před prvním dnem měsíce
        if ($firstDayOfMonth->format('D') === 'Mon') {
            $startDate = $firstDayOfMonth;
        }

        // Nastavení koncového data na poslední den příštího měsíce o rok později
        $endDate = (clone $firstDayOfMonth)->modify('+1 year')->modify('last day of this month');

        // Pole pro dny
        $days = [];

        // Procházení každého dne od počátečního do koncového data
        while ($startDate <= $endDate) {
            // Přidání dne do pole
            $days[$startDate->format('Y-m-d')] = $this->getDateSlots($startDate->format('Y-m-d'));
            
            // Posun na další den
            $startDate->modify('+1 day');
        }

        return $days;
    }

    public function isTimeAvaible($date, $minutes): array
    {
        $avaibleEmployes = [];
        $this->employes->each(function ($employeID) use (&$avaibleEmployes) {
            $avaibleEmployes[$employeID] = true;
        });

        if(!$this->employeCustomDates){
            // Změna dotazu na pracovní období tak, aby zahrnoval celý rok
            $specificDates = WorkPeriod::where(function ($query) use ($date) {
                $query->whereDate('custom_date', $date);
            })
            ->orWhereNull('custom_date')
            ->get()->toArray();
            $this->employeCustomDates = array_filter($specificDates, function ($item){
                return $this->employes->contains($item['agent_id']) && $item['service_id'] == 0 || $item['service_id'] == $this->service->id;
            });
        }
        if(!$this->employesBookings){
            // Změna dotazu tak, aby zahrnoval celý rok
            $bookings = Booking::where(function ($query) use ($date) {
                $query->whereDate('start_date', $date);
            })
            ->whereIn('agent_id', $this->employes)
            ->get()->toArray();

            $this->setEmployesBookings();

            foreach ($bookings as $item) {
                // Zkontrolujeme, zda existuje agent a datum v agentsBookings
                if (isset($this->employesBookings->{$item['agent_id']}->{$item['start_date']})) {
                    // Pokud existuje, přidáme nový booking
                    $this->employesBookings->{$item['agent_id']}->{$item['start_date']}[] = [
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time'],
                    ];
                } else {
                    // Pokud neexistuje, vytvoříme novou položku
                    $this->employesBookings->{$item['agent_id']}->{$item['start_date']} = [
                        [
                            'start_time' => $item['start_time'],
                            'end_time' => $item['end_time'],
                        ]
                    ];
                }
            }
         }


        // Zaměstnanec má volno
        foreach ($this->employeCustomDates as $item) {
            $datetime = new \DateTime($date);
            if((($item['custom_date'] == null) && $datetime->format('N') == $item['week_day']) || $item['custom_date'] == $date){
                $start = $item['start_time'];
                $end = $item['end_time'] != 0 ? $item['end_time'] : 1440;
                if ($start == 0 && $end == 1440 || $minutes < $start || $minutes > $start && (($minutes + $this->service->duration) > $end)) {
                    $avaibleEmployes[$item['agent_id']] = false;
                }
            }
        }

        
        foreach (array_keys(array_filter($avaibleEmployes)) as $employe) {
            // Získání všech rezervací pro zaměstnance na dané datum
            $thisDateEmployeBookings = $this->employesBookings->{$employe}->{$date} ?? [];

            // Procházení všech rezervací
            foreach ($thisDateEmployeBookings as $booking) {
                // Výpočet času, kdy nová služba začne a skončí
                $newServiceStart = $minutes;
                $newServiceEnd = $minutes + $this->service->duration;

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
        for ($currentMinutes = $startTime; $currentMinutes <= $endTime - $this->service->duration; $currentMinutes += $this->timeBlockInterval) {
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
