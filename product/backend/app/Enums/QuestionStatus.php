<?php

namespace App\Enums;

enum QuestionStatus: string
{
    case Answered = 'answered';
    case Escalated = 'escalated';
    case Unsupported = 'unsupported';
}
