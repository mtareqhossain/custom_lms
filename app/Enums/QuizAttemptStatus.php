<?php

namespace App\Enums;

enum QuizAttemptStatus: string
{
    case InProgress = 'in_progress';
    case PendingReview = 'pending_review'; // has short_text answers awaiting manual grading
    case Graded = 'graded';
}
