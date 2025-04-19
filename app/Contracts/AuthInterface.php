<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthInterface{
    public function register(array $data);
    public function authenticate(array $data);
    public function refreshToken(Request $request);
}
