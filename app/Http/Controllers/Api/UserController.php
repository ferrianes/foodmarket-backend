<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use PasswordValidationRules;

    public function login(Request $request)
    {
        try {
            // Validasi Input
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // Mengecek Credentials (login)
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            // Jika hash tidak sesuai maka beri error
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            // Jika berhasil maka loginkan
            $token_result = $user->createToken('auth_token')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $token_result,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authenticated Failed', 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:20',
                'email' => 'required|string|email|max:20|unique:users',
                'password' => $this->passwordRules()
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'house_number' => $request->house_number,
                'phone_number' => $request->phone_number,
                'city' => $request->city,
                'password' => Hash::make($request->password)
            ]);

            $token_result = $user->createToken('auth_token')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token_result,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Data Profile user berhasil diambil');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }

    public function updatePhoto(Request $request)
    {
        $validatior = Validator::make($request->all(), [
            'file' => 'required|image|max:2048'
        ]);

        if ($validatior->fails()) {
            return ResponseFormatter::error([
                'error' => $validatior->errors()
            ], 'Update Photo Fails', 401);
        }

        if ($request->file('file')) {
            $file = $request->file->store('assets/user', 'public');

            // Simpan foto ke database
            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([
                $file
            ], 'File Successfully Uploaded');
        }
    }
}
