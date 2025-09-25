<?php

namespace Larashield\Traits;

use Illuminate\Support\Carbon;

trait SerializesDateWithTimezone
{
    public function serializeDate(\DateTimeInterface $date)
    {
        return Carbon::parse($date)
            ->setTimezone(config('app.timezone'))
            ->format('d-m-Y H:i:s'); // e.g., 'd-m-Y'
    }
}
