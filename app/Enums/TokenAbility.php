<?php

namespace App\Enums;

enum TokenAbility: string{
    case ISSUE_ACCESS_TOKEN = 'issue-rt-token';
    case ACCESS_API = 'access-api';
}
