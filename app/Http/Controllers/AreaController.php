<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Area;
use Illuminate\Support\Facades\View;  
use DB; 
//use Auth;

//TODO Validera inkommande data för create/update/delete
class AreaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function __construct()
    {
        //definiera vilka anrop som behöver nyckel/autentisering
        $this->middleware('auth', ['only' => [
            'index','createArea', 'updateArea','deleteArea', 'search'
        ]]);
        //Skicka alla anrop till middleware som sätter locale utifrån parameter/header
        $this->middleware('localization');
        //Skicka alla till config middleware för att sätta t. ex vilken MRBS databas som ska anropas
        $this->middleware('config');
    }

    /*************************************************
     * 
     * Funktion som kan anropas utan några nycklar, 
     * se till att det inte finns några känsliga data
     * som returneras!
     * 
    **************************************************/
    public function noauthindex(Request $request)
    {
        $query = DB::table('mrbs_area')
            ->select('mrbs_area.*');

        if($request->input('id')) {
            $area_id = $request->input('id');
            $query = $query->when($area_id, function($q) use ($area_id) {
                return $q->where('id', '=', $area_id);
            });
        //area_id = false (ex vis "area_id=" eller "area_id=0" eller saknas helt)   
        } else {
            if ($request->input('_id')===0) {
                $query = $query->where('id', '=', 0);  
            }
        }

        if($request->input('limit')){
            $limit = $request->input('limit');
        } else {
            //visa 50 rader som default
            $limit = 50;
        }
        
        if (is_numeric($limit)){
            //return response()->json($query);
            return response()->json($query->orderBy('area_type')->take($limit)->get());
        } else {
            //returnera endast alla om parameter limit = none. Men med paginering
            return response()->json($query->orderBy('area_type')->paginate(100));
        }
    }

    /*************************************************
     *  
     * Funktion som kan anropas utan några nycklar, 
     * se till att det inte finns några känsliga data
     * som returneras!
     * 
    *************************************************/
    public function noauthgetArea($id)
    {
        $area = Area::find($id);

        if ( ! $area)
        {
            return response()->json(['response' => trans('messages.areanotfound')]);
        }

        $areawithareaname = DB::table('mrbs_area')
            ->select('mrbs_area.*')
            ->where('mrbs_area.id', '=', $area->id)
            ->first();
        return response()->json($areawithareaname);
    }

    public function index(Request $request)
    {
        $query = DB::table('mrbs_area')
            ->select('mrbs_area.*');

        if($request->input('id')) {
            $id = $request->input('id');
            $query = $query->when($id, function($q) use ($id) {
                return $q->where('id', '=', $aid);
            });
        //area_id = false (ex vis "area_id=" eller "area_id=0" eller saknas helt)   
        } else {
            if ($request->input('id')===0) {
                $query = $query->where('id', '=', 0);  
            }
        }

        if($request->input('limit')){
            $limit = $request->input('limit');
        } else {
            //visa 50 rader som default
            $limit = 50;
        }
        
        if (is_numeric($limit)){
            //return response()->json($query);
            return response()->json($query->orderBy('area_type')->take($limit)->get());
        } else {
            //returnera endast alla om parameter limit = none. Men med paginering
            return response()->json($query->orderBy('area_type')->paginate(100));
        }
    }

    public function getArea($id)
    {
        $area = Area::find($id);

        if ( ! $area)
        {
            return response()->json(['response' => trans('messages.areanotfound')]);
        }

        $areawithareaname = DB::table('mrbs_area')
            ->select('mrbs_area.*')
            ->where('mrbs_area.id', '=', $aera->id)
            ->first();
        return response()->json($areawithareaname);
    }

    public function createArea(Request $request)
    {
        $this->validate($request, [
            'area_name' => 'required'
        ]);

        $area = Area::create($request->all());
        //201 = http-statuskod för att nåt skapats.
        return response()->json($area, 201);
    }

    public function updateArea(Request $request, $id)
    {
        $area = Area::findOrFail($id);
        $area->area_name = $request->input('area_name');
        $area->save();
        return response()->json($area, 200);   
    }

    public function deleteArea($id){
        $area = Area::findOrFail($id);
        $area->delete();
        //200 Http-status OK
        return response('Deleted Successfully', 200);
    }
   
    public function search(Request $request)
    {
        $entry = ["status" => "No result", "message" => "Please apply filter"];
        $limit = 10;
        if ($request->has('limit')) { 
            $limit = $request->input('limit');
        }
        if ($request->has('id')) {   
            $entry = Entry::where('id', $request->input('id'))
            ->orderBy('area_type', 'desc')
            ->take($limit)
            ->get();
        }
        return response()->json($entry);
    }
}
?>