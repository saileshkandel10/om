<?php

namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;

class QuizAttemptPolicy
{
    /**
     * Determine whether the user can view the quiz attempt.
     */
    public function view(User $user, QuizAttempt $attempt): bool
    {
        return $attempt->isAccessibleBy($user);
    }

    /**
     * Determine whether the user can submit the quiz attempt.
     */
    public function submit(User $user, QuizAttempt $attempt): bool
    {
        return (int) $attempt->user_id === (int) $user->id
            && $attempt->status === 'in_progress';
    }

    /**
     * Determine whether the user can delete the attempt.
     */
    public function delete(User $user, QuizAttempt $attempt): bool
    {
        return $user->hasRole('admin');
    }
}
