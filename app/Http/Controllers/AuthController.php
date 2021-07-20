<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\User;

use App\Http\Requests;

class AuthController extends Controller
{
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|max:32|min:6',
        ]);

        if ($validator->fails()) {
            return Response::badRequest($validator->errors());
        }

        $payload = $request->all();
        if (!$token = \JWTAuth::attempt([
            'email' => $payload['email'],
            'password' => $payload['password']
        ])) {
            return response()->json([
                'message' => 'Wrong username or password.'
            ], 400);
        } else {
            return response()->json([
                'data' => [
                    'token' => $token,
                    'user' => auth()->user()
                ]
            ], 200);
        }
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required|max:32|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }

        $payload = $request->all();
        if (User::where("email", $payload["email"])->first()) {
            return response()->json([
                'message' => "Email is existed."
            ], 400);
        }

        $user = new User();
        $user->name = $payload['name'];
        $user->email = $payload['email'];
        $user->password = Hash::make($payload['password']);
        if ($user->save()) {
            $token = \JWTAuth::attempt([
                'email' => $payload['email'],
                'password' => $payload['password']
            ]);

            return response()->json([
                'data' => [
                    'token' => $token,
                    'user' => auth()->user()
                ]
            ], 200);
        } else {
            return response()->json([
                'message' => "Have an error on server."
            ], 500);
        }
    }
}
