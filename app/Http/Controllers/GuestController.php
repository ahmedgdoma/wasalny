<?php

namespace App\Http\Controllers;

use App\Guest;
use App\Travel;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class GuestController extends Controller
{
    /*
     * join travel api takes
     */
    /**
     * @param $request
     */
    public function joinTravel(Request $request){
        $join = new Guest;
        $join->user_id = $request->user_id;
        $join->travel_id = intval($request->travel_id);
        $travel = Travel::find($join->travel_id);
        $user = User::find($join->user_id);
        if(!$travel && !$user){
            return response()->json(array('result' => 0, 'message' => 'error user or travel not found'), 200);
        }else{
            $join->status = 1;
            $join->save();
            return response()->json(array('result' => 1, 'message' => 'request sent successfully'), 200);
        }
    }

    public function responseRequest(Request $request){
        $id = $request->id;
        $travel = Guest::find($id);
        if (!$travel){
            return response()->json(array('result' => 0, 'message' => 'travel not found'), 200);
        }else{
            $status = $request->status;
            $travel->update(['status'=> $status]);
            return response()->json(array('result' => 1, 'message' => 'request responded successfully'), 200);
        }
    }
}
