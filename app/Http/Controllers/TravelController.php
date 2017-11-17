<?php

namespace App\Http\Controllers;

use App\City;
use App\Station;
use App\Travel;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class TravelController extends Controller
{
    /**
     * Api Functions
     * @param Request $request
     */
    public function setTravel(Request $request){
        if($request->user_id != '' && $request->travel_name != '' && $request->start_time != '' && $request->capacity != '' && $request->stations != ''
           && $request->passenger_gender != '' && $request->status != '' && $request->days != '' && $request->payment != '' && $request->city_id!= ''
            && $request->cost != ''){
            $travel = new Travel();
            $travel->user_id = $request->user_id;
            if (!User::find($travel->user_id)){
                return response()->json(array('result' => 0, 'message' => 'user not found'), 200);
                die;
            }
            if (!City::find($request->city_id)){
                return response()->json(array('result' => 0, 'message' => 'city not found'), 200);
                die;
            }
            $travel->travel_name= $request->travel_name;
            $travel->city_id=$request->city_id;
            $travel->start_time= $request->start_time;
            $travel->capacity= $request->capacity;
            $travel->passenger_gender= $request->passenger_gender;
            $travel->status= $request->status;
            $travel->status= $request->cost;
            $travel->status= $request->payment;
            $travel->days= $request->days;
            if(isset($request->notes)){
                $travel->status= $request->notes;
            }
            $request->stations= json_decode($request->stations);
            $stations = $request->stations;
            foreach ($stations as $key => $station){
                if(!Station::find($station)){
                    return response()->json(array('result' => 0, 'message' => 'station #'.$key. ' not found'), 200);
                    die;
                }
            }
            $travel->save();
            foreach ($request->stations as $station){
                $travel->stations()->attach($station);
            }
            return response()->json(array('result' => 1, 'message' => 'created travel successfully'), 200);

        }elseif ($request->user_id == ''){
            return response()->json(array('result' => 0, 'message' => 'user_id not found'), 200);
        }elseif ($request->travel_name == ''){
            return response()->json(array('result' => 0, 'message' => 'travel_name not found'), 200);
        }elseif ($request->start_time == ''){
            return response()->json(array('result' => 0, 'message' => 'start_time not found'), 200);
        }elseif ($request->capacity == ''){
            return response()->json(array('result' => 0, 'message' => 'capacity not found'), 200);
        }elseif ($request->passenger_gender == ''){
            return response()->json(array('result' => 0, 'message' => 'passenger_gender not found'), 200);
        }elseif ($request->status == ''){
            return response()->json(array('result' => 0, 'message' => 'status not found'), 200);
        }elseif ($request->stations == ''){
            return response()->json(array('result' => 0, 'message' => 'stations not found'), 200);
        }elseif ($request->days == ''){
            return response()->json(array('result' => 0, 'message' => 'days not found'), 200);
        }elseif ($request->payment == ''){
            return response()->json(array('result' => 0, 'message' => 'payment not send'), 200);
        }elseif ($request->cost == ''){
            return response()->json(array('result' => 0, 'message' => 'cost not send'), 200);
        }elseif (empty($request->city_id)){
            return response()->json(array('result' => 0, 'message' => 'city not send'), 200);
        }
    }

    /**
     * update travel api
     * @param Request $request
     */
    public function updateTravel(Request $request){
        $id = $request->id;
        $travel = Travel::find($id);
        if($travel) {
            $stations= json_decode($request->stations);
            $travel->stations()->sync($stations);
            $r = $request->all();
            unset($r['stations']);
            $travel->update($r);
            return response()->json(array('result' => 1, 'message' => 'updated travel successfully'), 200);
        }else{
            return response()->json(array('result' => 0, 'message' => 'travel not found'), 200);
        }
    }

    public function viewTravel(Request $request){
        $id = $request->id;
        if(!$id){
            return response()->json(array('result' => 0, 'message' => 'parameter not found'));
        }else{
            $travel = Travel::find($id);
            if(!$travel){
                return response()->json(array('result' => 0, 'message' => 'travel not found'));
            }else {
                $mytravel = Travel::find($id)->with('user', 'city', 'stations')->get();
                return response()->json(array('result' => 1, 'message' => 'travels views successfully', 'data' => $mytravel));
            }
        }
    }

    public function viewMyTravels(Request $request){
        $id = $request->user_id;
        if (!isset($id) || empty($id) || !User::find($id)){
            return response()->json(array('result' => 0, 'message' => 'user not found'), 200);
        }else{
            $travel = Travel::where('user_id', $id)->paginate(10);
            return response()->json(array('result' => 1, 'message' => 'travels views successfully', 'data'=> $travel), 200);
        }
    }

    public function getTravelsByCity(Request $request)
{
     $city_id = $request->city_id;
     $user_id = $request->user_id;
    if($city_id && $user_id){
        $city = City::find($city_id);
        $user = User::find($user_id);
        if(!$city){
            return response()->json(array('result' => 0, 'message' => 'city not found'), 200);
        }elseif(!$user){
            return response()->json(array('result' => 0, 'message' => 'user not found'), 200);
        }else{
            $travels = Travel::where('city_id' ,$city_id)->where('user_id', $user_id)->with('user')->paginate(10);
            return response()->json(array('result' => 1, 'message' => 'travels views successfully', 'data'=> $travels), 200);
        }

    }elseif ($city_id && !$user_id){
        $city = City::find($city_id);
        if(!$city){
            return response()->json(array('result' => 0, 'message' => 'city not found'), 200);
        }else{
            $travels = Travel::where('city_id' ,$city_id)->with('user')->paginate(10);
            return response()->json(array('result' => 1, 'message' => 'travels views successfully', 'data'=> $travels), 200);}
    }elseif(!$city_id && $user_id){
        $user = User::find($user_id);
        if(!$user){
            return response()->json(array('result' => 0, 'message' => 'user not found'), 200);
        }else{
            $travels = Travel::where('user_id' ,$user_id)->with('user')->paginate(10);
            return response()->json(array('result' => 1, 'message' => 'travels views successfully', 'data'=> $travels), 200);}
    }else{
        return response()->json(array('result' => 0, 'message' => 'user and city not found'), 200);

    }

}

    public function changeStatus(Request $request){
        $travel = Travel::find($request->id);
        $status = $request->status;
        if(!$travel || $travel->user_id != $request->user_id){
            return response()->json(array('result' => 0, 'message' => 'travel not found'), 200);
        }else{
            $travel->update(['status'=> $status]);
            return response()->json(array('result' => 1, 'message' => 'travels updated successfully', 'data'=> $travel), 200);
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $travel = Travel::latest()->first()->start_time;
        echo $travel;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
