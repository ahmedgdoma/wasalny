<?php

namespace App\Http\Controllers;

use App\Station;
use App\City;
use Illuminate\Http\Request;

use App\Http\Requests;

class CityController extends Controller
{
    /**
     * Api Functions
     * @param Request $request
     */
    public function getCity(){
      $Cities = City::all();
      if(count($Cities))
      {
      return response()->json([
                        'result' => '1',
                        'data' => $Cities,
                        'message' => 'success'
                    ]);
      }else{
      return response()->json([
                        'result' => '2',
                        'message' => 'No city found'
                    ]);
      }
    }

    


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
