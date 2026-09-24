<?php

namespace App\Enums;

enum UserType: int
{
    case STUDENT = 1;
    case SCHOOL = 2;
    case TRAINER = 3;
}