<?php

namespace App\Events;

interface Announceable
{
    public function message(): array;
}
