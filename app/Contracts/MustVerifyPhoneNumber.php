<?php

namespace App\Contracts;

interface MustVerifyPhoneNumber
{
    //
    /**
     * Determine if the user has verified their phone number.
     *
     * @return bool
     */
    public function hasVerifiedPhoneNumber(): bool;
    /**
     * Mark the authenticated user's phone number as verified.
     *
     * @return bool
     */
    public function markPhoneNumberAsVerified(): bool;
    /**
     * Send the phone number verification notification.
     *
     * @return void
     */
    public function sendPhoneNumberVerificationNotification(): void;
    /**
     * Get the phone number that should be used for verification.
     *
     * @return string
     */
    public function getPhoneNumberForVerification();
}
