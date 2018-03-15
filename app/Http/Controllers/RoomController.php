<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Room;
use Illuminate\Support\Facades\View;  
use DB; 
//use Auth;

//TODO Validera inkommande data för create/update/delete
class RoomController extends Controller
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
            'index', 'getRoom', 'createRoom', 'updateRoom','deleteRoom', 'search'
        ]]);
        //Skicka alla anrop till middleware som sätter locale utifrån parameter/header
        $this->middleware('localization');
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
        $query = DB::table('mrbs_room')
            ->select('mrbs_room.*');

        if($request->input('area_id')) {
            $area_id = $request->input('area_id');
            $query = $query->when($area_id, function($q) use ($area_id) {
                return $q->where('area_id', '=', $area_id);
            });
        //area_id = false (ex vis "area_id=" eller "area_id=0" eller saknas helt)   
        } else {
            if ($request->input('area_id')===0) {
                $query = $query->where('area_id', '=', 0);  
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
            return response()->json($query->orderBy('sort_key')->take($limit)->get());
        } else {
            //returnera endast alla om parameter limit = none. Men med paginering
            return response()->json($query->orderBy('sort_key')->paginate(100));
        }
    }

    /*************************************************
     *  
     * Funktion som kan anropas utan några nycklar, 
     * se till att det inte finns några känsliga data
     * som returneras!
     * 
    *************************************************/
    public function noauthgetRoom($id)
    {
        $room = Room::find($id);

        if ( ! $room)
        {
            return response()->json(['response' => trans('messages.roomnotfound')]);
        }

        $roomwithroomname = DB::table('mrbs_room')
            ->select('mrbs_room.*')
            ->where('mrbs_room.id', '=', $room->id)
            ->first();
        return response()->json($roomwithroomname);
    }

    /*************************************************
     *  
     * Funktion som kan anropas utan några nycklar, 
     * se till att det inte finns några känsliga data
     * som returneras!
     * 
     * Används för att visa rumstatus på
     * smartsign-skärmar
     * 
    *************************************************/
    public function noauthgetRoomAvailability(Request $request)
    {
        if ($request->has('timestamp')) { 
            $timestamp = $request->input('timestamp');
        } else {
            return response()->json(['response' => 'Please provide timestamp']);
        }
        $arr = array();

        $Data = DB::table('mrbs_room')->where('mrbs_room.area_id', '=', '2')->orderBy('sort_key')->get();
        foreach ($Data as $data) {
            $roomwithroomname = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.*')
            ->where('mrbs_room.id', '=', $data->id)
            ->where('mrbs_entry.start_time', '<=', $timestamp)
            ->where('mrbs_entry.end_time', '>', $timestamp)
            
            ->count();
            if ($roomwithroomname == 0){
                $arr[] = ['room_number' => $data->room_number, 'room_name' => $data->room_name, 'availability' => 'true'];
            } else {
                $arr[] = ['room_number' => $data->room_number, 'room_name' => $data->room_name, 'availability' => 'false'];
            }
           
        }
        return response()->json($arr);
    }

    public function index(Request $request)
    {
        $query = DB::table('mrbs_room')
            ->select('mrbs_room.*');

        if($request->input('area_id')) {
            $area_id = $request->input('area_id');
            $query = $query->when($area_id, function($q) use ($area_id) {
                return $q->where('area_id', '=', $area_id);
            });
        //area_id = false (ex vis "area_id=" eller "area_id=0" eller saknas helt)   
        } else {
            if ($request->input('area_id')===0) {
                $query = $query->where('area_id', '=', 0);  
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
            return response()->json($query->orderBy('sort_key')->take($limit)->get());
        } else {
            //returnera endast alla om parameter limit = none. Men med paginering
            return response()->json($query->orderBy('sort_key')->paginate(100));
        }
    }

    public function getRoom($id)
    {
        $room = Room::find($id);

        if ( ! $room)
        {
            return response()->json(['response' => trans('messages.bookingnotfound')]);
        }

        $roomwithroomname = DB::table('mrbs_room')
            ->select('mrbs_room.*')
            ->where('mrbs_room.id', '=', $room->id)
            ->first();
        return response()->json($roomwithroomname);
    }

    public function createRoom(Request $request)
    {
        $this->validate($request, [
            'room_name' => 'required'
        ]);

        $room = Room::create($request->all());
        //201 = http-statuskod för att nåt skapats.
        return response()->json($entry, 201);
    }

    public function updateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->room_name = $request->input('room_name');
        $room->save();
        return response()->json($room, 200);   
    }

    public function deleteRoom($id){
        $room = Room::findOrFail($id);
        $room->delete();
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
        if ($request->has('area_id')) {   
            $entry = Entry::where('area_id', $request->input('area_id'))
            ->orderBy('sort_key', 'desc')
            ->take($limit)
            ->get();
        }
        return response()->json($entry);
    }
}
?>