<?php

namespace App\Http\Controllers\api;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;


class AuthenticationController extends Controller
{


    public function mobileLogin(Request $request){
        $user = User::with(['rol'])->where('email', $request['email'])->first();

        if(!$user){
            return response()->json(['message' => 'Por favor, revise los datos ingresados'], 422);
        }

        if (!$user || !Hash::check($request->password, $user->password) || $user->rol->nombre != RolesEnum::TRANSMISOR) {
            return response()->json(['message' => 'Verifique los datos ingresados e intente nuevamente'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
                'access_token' => $token,
                'user' => $user
        ]);

    }

}
