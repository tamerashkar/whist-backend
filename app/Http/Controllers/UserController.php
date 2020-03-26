<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use Laravel\Passport\Http\Controllers\AccessTokenController;

class UserController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user()->load('player'));
    }

    public function store(StoreUserRequest $request)
    {
        if ((new User())->findForPassport($request->username)) {
            return $this->login($request);
        } else {
            return $this->register($request);
        }
    }

    public function login(StoreUserRequest $request)
    {
        $controller = app(AccessTokenController::class);
        return App::call([$controller, 'issueToken']);
    }

    public function register(StoreUserRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return $this->login($request);
    }
}
