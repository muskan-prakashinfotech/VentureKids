<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ExternalSession extends Model
{
    protected $table = 'external_session';

    protected $fillable = [
        'title',
        'created_by',
        'session_type',
        'agenda',
        'date_time',
        'zoom_link',
        'send_zoom_link',
        'speaker',
        'attendees',
        'attendee_type',
        'trainer_ids',
        'levels',
        'batches',
        'notify_recipients',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_days',
        'recurrence_end_date',
        'recurrence_count',
        'recurrence_custom_dates',
        'parent_session_id',
        'is_exception',
        'original_date_time',
        'is_cancelled',
    ];

    protected $casts = [
        'send_zoom_link' => 'boolean',
        'recurrence_interval' => 'integer',
        'recurrence_count' => 'integer',
        'parent_session_id' => 'integer',
        'is_exception' => 'boolean',
        'is_cancelled' => 'boolean',
    ];

    public function isRecurring(): bool
    {
        return !empty($this->recurrence_type) && $this->recurrence_type !== 'none';
    }

    public function recurrenceDaysArray(): array
    {
        if (empty($this->recurrence_days)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->recurrence_days)));
    }

    public function recurrenceCustomDatesArray(): array
    {
        if (empty($this->recurrence_custom_dates)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', $this->recurrence_custom_dates)))));
    }

    public function recurrenceSummary(): string
    {
        if (!$this->isRecurring()) {
            return '';
        }

        $interval = $this->recurrence_interval ?: 1;
        $suffix = $interval > 1 ? "every {$interval} " : 'every ';
        $type = $this->recurrence_type;

        if ($type === 'weekly') {
            $days = $this->recurrenceDaysArray();
            $daysText = $days ? implode(', ', array_map('ucfirst', $days)) : 'weekly';
            $ruleText = $suffix . 'week on ' . $daysText;
        } elseif ($type === 'daily') {
            $ruleText = $suffix . 'day';
        } elseif ($type === 'monthly') {
            $ruleText = $suffix . 'month';
        } elseif ($type === 'custom') {
            $dates = $this->recurrenceCustomDatesArray();
            $datesText = $dates
                ? implode(', ', array_map(fn ($d) => Carbon::parse($d)->format('d M Y'), $dates))
                : 'no additional dates';
            return 'Custom dates: ' . $datesText;
        } else {
            $ruleText = ucfirst($type);
        }

        $parts = [$ruleText];
        if ($this->recurrence_end_date) {
            $parts[] = 'until ' . Carbon::parse($this->recurrence_end_date)->format('d M Y');
        } elseif ($this->recurrence_count) {
            $parts[] = 'for ' . $this->recurrence_count . ' occurrences';
        }

        return implode(', ', $parts);
    }

    public function exceptionDateTimes(): array
    {
        if (!$this->id) {
            return [];
        }

        return self::where('parent_session_id', $this->id)
            ->where('is_exception', true)
            ->pluck('original_date_time')
            ->map(function ($dateTime) {
                return Carbon::parse($dateTime, 'UTC')->format('Y-m-d H:i:s');
            })->toArray();
    }

    public function isOccurrenceCancelled(Carbon $dateTime): bool
    {
        if ($this->is_cancelled) {
            return true;
        }

        $exceptions = array_flip($this->exceptionDateTimes());

        return isset($exceptions[$dateTime->copy()->timezone('UTC')->format('Y-m-d H:i:s')]);
    }

    /**
     * Find the next active (non-cancelled) occurrence at or after $from.
     * Used to anchor/continue the self-chaining reminder-email jobs.
     */
    public function nextOccurrenceFrom(Carbon $from): ?Carbon
    {
        if ($this->is_cancelled || $this->is_exception) {
            return null;
        }

        $searchTo = $this->recurrence_end_date
            ? Carbon::parse($this->recurrence_end_date, 'UTC')->endOfDay()
            : $from->copy()->addYears(2);

        return $this->occurrenceDateTimes($from, $searchTo)
            ->first(function (Carbon $occurrence) use ($from) {
                return $occurrence->gte($from);
            });
    }

    public function occurrenceDateTimes(Carbon $from, Carbon $to): Collection
    {
        if ($this->is_cancelled || $this->is_exception) {
            return collect();
        }

        $start = Carbon::parse($this->date_time, 'UTC');
        $from = $from->copy()->startOfDay();
        $to   = $to->copy()->endOfDay();

        $exceptions = array_flip($this->exceptionDateTimes());

        if (!$this->isRecurring()) {
            return collect([$start])->filter(function (Carbon $dateTime) use ($from, $to, $exceptions) {
                return $dateTime->between($from, $to) && !isset($exceptions[$dateTime->format('Y-m-d H:i:s')]);
            });
        }

        $occurrences = collect();
        $interval = max(1, $this->recurrence_interval ?? 1);
        $limitCount = $this->recurrence_count ?: 500;
        $endDate = $this->recurrence_end_date ? Carbon::parse($this->recurrence_end_date, 'UTC')->endOfDay() : null;
        $count = 0;

        if ($this->recurrence_type === 'daily') {
            $current = $start->copy();
            while ($count < $limitCount && (!$endDate || $current->lte($endDate)) && $current->lte($to)) {
                if ($current->gte($from)) {
                    $formatted = $current->format('Y-m-d H:i:s');
                    if (!isset($exceptions[$formatted])) {
                        $occurrences->push($current->copy());
                    }
                }
                $current->addDays($interval);
                $count++;
            }
        } elseif ($this->recurrence_type === 'weekly') {
            $selectedDays = $this->recurrenceDaysArray();
            $selectedDays = array_map('strtolower', $selectedDays);
            $weekDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            if (empty($selectedDays)) {
                $selectedDays = [strtolower($start->format('l'))];
            }
            $dayIndexes = array_flip($weekDays);
            $weekStart = $start->copy()->startOfWeek();
            $weekNumber = 0;

            while ($count < $limitCount) {
                foreach ($selectedDays as $day) {
                    if (!isset($dayIndexes[$day])) {
                        continue;
                    }
                    $occurrence = $weekStart->copy()
                        ->addWeeks($weekNumber)
                        ->addDays($dayIndexes[$day])
                        ->setTimeFrom($start);

                    if ($occurrence->lt($start)) {
                        continue;
                    }
                    if ($endDate && $occurrence->gt($endDate)) {
                        continue;
                    }
                    if ($occurrence->gt($to)) {
                        continue;
                    }

                    $formatted = $occurrence->format('Y-m-d H:i:s');
                    if (!isset($exceptions[$formatted]) && $occurrence->gte($from)) {
                        $occurrences->push($occurrence->copy());
                    }
                    $count++;
                    if ($count >= $limitCount) {
                        break 2;
                    }
                }

                $weekNumber += $interval;
                if ($weekStart->copy()->addWeeks($weekNumber)->gt($to)) {
                    break;
                }
            }
        } elseif ($this->recurrence_type === 'monthly') {
            $current = $start->copy();
            while ($count < $limitCount && (!$endDate || $current->lte($endDate)) && $current->lte($to)) {
                if ($current->gte($from)) {
                    $formatted = $current->format('Y-m-d H:i:s');
                    if (!isset($exceptions[$formatted])) {
                        $occurrences->push($current->copy());
                    }
                }
                $current->addMonths($interval);
                $count++;
            }
        } elseif ($this->recurrence_type === 'custom') {
            $dates = collect($this->recurrenceCustomDatesArray())
                ->push($start->format('Y-m-d'))
                ->unique()
                ->map(function ($date) use ($start) {
                    return Carbon::parse($date, 'UTC')->setTimeFrom($start);
                })
                ->sort();

            foreach ($dates as $occurrence) {
                if ($occurrence->lt($from) || $occurrence->gt($to)) {
                    continue;
                }
                $formatted = $occurrence->format('Y-m-d H:i:s');
                if (!isset($exceptions[$formatted])) {
                    $occurrences->push($occurrence->copy());
                }
            }
        }

        return $occurrences->unique(function (Carbon $dateTime) {
            return $dateTime->format('Y-m-d H:i:s');
        })->sort();
    }

    public static function occurrencesForSessions(Collection $sessions, Carbon $from, Carbon $to): Collection
    {
        return $sessions->flatMap(function (ExternalSession $session) use ($from, $to) {
            return $session->occurrenceDateTimes($from, $to)->map(function (Carbon $dateTime) use ($session) {
                return [
                    'session' => $session,
                    'date_time' => $dateTime,
                ];
            });
        })->sortBy(function ($entry) {
            return $entry['date_time']->timestamp;
        })->values();
    }
}
