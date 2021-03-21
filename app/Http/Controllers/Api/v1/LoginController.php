<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Carbon\Carbon;
use App\User;
use App\Models\Tokens;
use App\Models\ForgotPassword;
use Illuminate\Support\Facades\DB;
use App\Mail\RegisterMail;
use App\Mail\ForgotPasswordMail;
use Mail;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /*
    |-----------------------------------------------------
    | LOGIN API FUNCTION
    |-----------------------------------------------------
    */
    public function sendMail() {
        Mail::raw('Text to e-mail', function ($message) {
            $message->from(env('MAIL_FROM_ADDRESS'), $name = env('MAIL_FROM_NAME'));
            $message->to('m.umer1076@gmail.com');
            $message->subject("Test Email");
        });
    }
    /*
    |-----------------------------------------------------
    | LOGIN API FUNCTION
    |-----------------------------------------------------
    */
    public function login(LoginRequest $request)
    {
        if (auth()->attempt($this->constructCredentials($request))) {
            $user = auth()->user();
            $token = strtoupper(base64_encode($user->email)."-".uniqid());
            // update/create user token
            Tokens::updateOrCreate(['user_id' => $user->id], ['api_token' => $token]);
            // update user location if given
            if($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
                User::where(['id' => $user->id])->update(['latitude' => $request->latitude, 'longitude' => $request->longitude]);
            }
            $result = $this->prepareSuccessResult($user);
            return response()->json($result, $result['status']);
        }
        $result = $this->prepareErrorResult();
        return response()->json($result, $result['status']);
    }

    /*
    |-----------------------------------------------------
    | REGISTER API FUNCTION
    |-----------------------------------------------------
    */
    public function register(RegisterRequest $request)
    {
        $error = "Account couldn\'t created, Error occured";
        \DB::beginTransaction();
        $user = new User($request->all());
        $response = array();
        try {
            $user->user_role = $request->account_type;
            $user->password = Hash::make($user->password);
            $response = $user->save()
                ? ['user_id' => $user->id, 'code' => 200] : $error;

            // create token for user
            $token = strtoupper(base64_encode($user->email)."-".uniqid());
            Tokens::create(['api_token' => $token, 'user_id' => $user->id]);
            \DB::commit();
            Mail::to($user->email)->send(new RegisterMail($user));
            return response()->json(array(
                    'user'    => $user,
                    'token'   => $token,
                    'user_id' => $user->id,
                    'message' => 'Your account successfully created',
                    'status'  => 200
                ), 200
            );
        }
        catch (\Exception $req) {
            \DB::rollBack();
            return response()->json(array('error' => $req->getMessage(), 'status'  => 401), 401);
        }
    }

    /*
    |-----------------------------------------------------
    | LOGOUT API FUNCTION
    |-----------------------------------------------------
    */
    public function logout(Request $request)
    {
        // update/create user token
        $user = $request->user;
        $result = Tokens::where(['user_id' => $user->id])->update(['api_token' => '']);
        User::where(['id' => $user->id])->update(['latitude' => '', 'longitude' => '']);
        return $result ? response()->json(['logged_out' => true, 'message' => 'You have logged out successfully', 'status' => 200], 200) : response()->json(['error' => 'Error occurs', 'status' => 409], 409);
    }

    /*
    |-----------------------------------------------------
    | FORGOT PASSWORD SEND EMAIL API FUNCTION
    |-----------------------------------------------------
    */
    public function forgotPasswordEmail(Request $request)
    {
        if(!$request->has('email')) {
            return response()->json(['status' => 400, 'error' => 'Please enter email to reset your password'], 400);
        }
        else {
            $user = User::where(['email' => $request->email])->first();
            if($user) {
                $token = str_replace("=", "", base64_encode($user->email)."-".uniqid());
                $url = env('FRONTEND_FORGOTPASS_URL').'?email='.$user->email.'&token='.$token;
                $expires_at = (Carbon::now())->addHours(3);
                $is_inserted = ForgotPassword::updateOrCreate(['user_id' => $user->id], ['token' => $token, 'expires_at' => $expires_at]);
                if($is_inserted) {
                    Mail::to($user->email)->send(new ForgotPasswordMail($user, $url));
                    return response()->json(['status' => 200, 'message' => 'A password reset link has been sent to your email address, please open that link and follow instructions to reset your password'], 200);
                }
                else {
                    return response()->json(['status' => 400, 'error' => 'Unable to send reset password email at the moment'], 400);    
                }
            }
            else {
                return response()->json(['status' => 400, 'error' => 'Requested email doesn`t exist'], 400);
            }
        }
    }

    /*
    |-----------------------------------------------------
    | SAVE RESET PASSWORD API FUNCTION
    |-----------------------------------------------------
    */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where(['email' => $request->email])->first();
        if($user) {
            $token = $request->token;
            $tokenData = ForgotPassword::where(['user_id' => $user->id, 'token' => $request->token])->first();
            if($tokenData) {
                $password = Hash::make($request->password);
                $is_updated = User::where(['id' => $user->id])->update(['password' => $password]);
                if($is_updated) {
                    ForgotPassword::where(['user_id' => $user->id])->delete();
                    return response()->json(['status' => 200, 'message' => 'Your password successfully changed, you can login with new password now'], 200);
                }
                else {
                    return response()->json(['status' => 400, 'error' => 'Unable to send reset password email at the moment'], 400);    
                }
            }
            else {
                return response()->json(['status' => 400, 'error' => 'Unable to reset your password, token expired or invalid'], 400);
            }
        }
        else {
            return response()->json(['status' => 400, 'error' => 'Requested user doesn`t exist'], 400);
        }
    }

    /*
    |-----------------------------------------------------
    | PRIVATE HELPER FUNCTIONS
    |-----------------------------------------------------
    */
    private function constructCredentials($request): array
    {
        return [
            'email' => $request->email,
            'password' => $request->password,
        ];
    }

    private function prepareErrorResult(): array
    {
        return [
            'error' => 'Account details do not match, please check email and password!',
            'status' => 401
        ];
    }

    private function prepareSuccessResult(User $user): array
    {
        return [
            'user' => [
                'authenticated' => true,
                'api_token' => $user->getToken->api_token,
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
                'user_role' => $user->user_role,
                'user_role_name' => $user->getRole->name,
                'vehicle' => $user->vehicle
            ],
            'message' => 'You have logged in successfully',
            'status' => 200
        ];
    }
}
