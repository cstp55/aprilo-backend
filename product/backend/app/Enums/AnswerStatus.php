<?php

namespace App\Enums;

enum AnswerStatus: string
{
    case Answered = 'answered';
    case Fallback = 'fallback';
    case Escalated = 'escalated';
}
