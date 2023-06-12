<?php

namespace Igniter\Local\Contracts;

interface WorkingHourInterface
{
    public function getDay();

    public function getOpen();

    public function getClose();

    public function isEnabled();
}
