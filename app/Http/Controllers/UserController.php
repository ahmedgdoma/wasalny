<?php
/**
 * Created by PhpStorm.
 * User: Aya
 * Date: 8/29/2017
 * Time: 9:22 AM
 */

namespace App\Http\Controllers;

use App\City;
use App\User;
use  Illuminate\Http\Request;
use Hash;

class UserController extends Controller
{
    public function signUp(Request $request)
    {
        if ($request->username && $request->phone && $request->password
        ) {

            $username = filter_var($request->username, FILTER_SANITIZE_STRING);
            $phone = filter_var($request->phone, FILTER_SANITIZE_NUMBER_INT);
            
            if(!empty($phone)){
            $password = $request->password;
            $checkuserphone = User:: where('phone', '=', $phone)->where('type', 'user')->first();
            if ((count($checkuserphone) < 1)) {
                if (strlen($password) >= 6) {
                    $user = new User();
                    $user->username = $username;
                    $user->phone = $phone;
                    $user->password = bcrypt($password);
                    if(isset($request->city)){
                    $user->city=$request->city;
                      }
                    $user->type = 'user';
                    $user->save();
                    return response()->json([
                        'result' => '1',
                        'data' => $user,
                        'message' => 'success'
                    ]);
                } else {
                    return response()->json([
                        'result' => '2',
                        'message' => 'Password must be more than 6 charachters '
                    ]);
                }

            } else {
                return response()->json([
                    'result' => '3',
                    'message' => ' phone is exist  before'
                ]);
            }
            }else{
               return response()->json([
                    'result' => '4',
                    'message' => ' phone is empty'
                ]);
            }
        } else {
            return response()->json([
                'result' => '0',
                'message' => 'Please enter data'
            ]);
        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(Request $request)
    {
        if (isset($request->password) && isset($request->phone)) {
            $phone = $request->phone;
            $password = $request->password;
            $user = User:: where('phone', '=', $phone)->where('type', 'user')->first();
            if ($user) {
                if (Hash::check($password, $user->password)) {
                    return response()->json([
                        'data' => $user,
                        'message' => 'success',
                        'result' => '1'
                    ]);
                } else {
                    return response()->json([
                        'result' => '0',
                        'message' => 'password not match'
                    ]);
                    //  $user = Auth::getUser();
                }
            } else {
                return response()->json([
                    'result' => '0',
                    'message' => 'Data not valid'
                ]);
            }
        } else {
            return response()->json([
                'result' => '0',
                'message' => 'Please enter data'
            ]);
        }
    }

    public function setCityForUser(Request $request){
        $id = $request->id;
        $city = $request->city;
        if(!isset($id) || empty($id)){
            return response()->json(array('result' => 0,'message' => 'user not found'));
        }else{
            $user = User::find($id);
            if(!$user){
                return response()->json(array('result' => 0,'message' => 'user not found'));
            }else{
                if(!isset($city) || empty($city)){
                    return response()->json(array('result' => 0,'message' => 'city not found'));
                }else{
                    $city_model = City::find($city);
                    if(!$city_model){
                        return response()->json(array('result' => 0,'message' => 'city not found'));
                    }else{
                        $user->update(['city_id'=> $city]);
                        return response()->json(array('result' => 1,'message' => 'updated successfully'));
                    }
                }
            }
        }
    }

    public function updateProfile(Request $request){
        $id = $request->id;
        $user = User::find($id);
        if(!$user || !isset($id) || empty($id)){
            return response()->json(array('result' => 0,'message' => 'user not found'));
        }else{
            $update = $request->all();
            if (isset($request->password)){
                $update['password'] = bcrypt($update['password']);
            }
            $user->update($update);
            return response()->json(array('result' => 1,'message' => 'successfully updated'));
        }
    }
    public function viewProfile(Request $request){
        $id = $request->id;
        if (!empty($id)) {
            $user = User::find($id);
            if (!$user){
                return response()->json(array('result' => 0,'message' => 'user not found'));
            }else{
                $user = User::find($id)->with('city')->get();
                return response()->json(array('result' => 1,'message' => 'successfully got user', 'data' => $user));
            }
        }else{
            return response()->json(array('result' => 0,'message' => 'user not found'));
        }
    }
}