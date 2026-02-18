<?php

Namespace Simplydigital\UserManagement\Http\Controllers;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;


class PrivateImageController
{
    public function show($path): StreamedResponse
    {
            
        abort_unless(Auth::check(), 403);
        
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->response($path);

    }    
}   