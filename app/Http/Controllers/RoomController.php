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
            '', 'index', '', 'createRoom', 'updateRoom','deleteRoom', 'search'
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

        if ($request->has('area_id')) { 
            $area_id = $request->input('area_id');
        } else {
            return response()->json(['response' => 'Please provide area_id']);
        }

        $arr = array();

        //Hämta area
        $area = DB::table('mrbs_area')
            ->select('mrbs_area.*')
            ->where('mrbs_area.id','=', $area_id)
            ->first();

        //Hämta rum
        $Data = DB::table('mrbs_room')
            ->whereIn('mrbs_room.area_id', [$area_id])
            //->where('mrbs_room.area_id', '=', '2')
            ->orderBy('sort_key')->get();

        //Gå igenom alla rum och kontrollera om status för aktuell timme
        foreach ($Data as $data) {
            $roomwithroomname = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.*')
            ->where('mrbs_room.id', '=', $data->id)
            ->where('mrbs_entry.start_time', '<=', $timestamp)
            ->where('mrbs_entry.end_time', '>', $timestamp)
            ->first();
            
            //om timestamp är utanför öppettider(<$area->morningstarts ELLER >$area->eveningends) för rummen så returnera status unavailable
            if(date('G',$timestamp) < $area->morningstarts || date('G',$timestamp) > $area->eveningends ){
                $arr[] = ['room_number' => $data->room_number, 'room_name' => $data->room_name, 'disabled' => $data->disabled, 'availability' => true, 'status' => 'unavailable'];
            } else {
                if (!$roomwithroomname){
                    $arr[] = ['room_number' => $data->room_number, 'room_name' => $data->room_name, 'disabled' => $data->disabled, 'availability' => true, 'status' => 'free'];
                } else {
                    //4=preliminär, 0=kvitterad
                    if ($roomwithroomname->status == 0 ){
                        // om type = "C" så returnera status unavailable
                        if ($roomwithroomname->type == 'C' ){
                            $status = "unavailable";
                        } else {
                            $status = "confirmed";
                        } 
                    }
                    if ($roomwithroomname->status == 4 ){
                        //om inom 15 minuter före/efter starttiden
                        //if ($timestamp > $roomwithroomname->start_time -15*60 && $timestamp < $roomwithroomname->start_time +15*60) {
                        if ($timestamp < $roomwithroomname->start_time +15*60) {
                            $status = "tobeconfirmed";
                        } else {
                            $status = "tentative";
                        }
                    }
                    $arr[] = ['room_number' => $data->room_number, 'room_name' => $data->room_name, 'disabled' => $data->disabled, 'availability' => false, 'status' => $status];
                }
            }
        }
        return response()->json($arr);
    }

    public function getRoomBookings(Request $request)
    {
        if ($request->has('bookingdate')) { 
            $bookingdate = $request->input('bookingdate');
        } else {
            return response()->json(['response' => 'Please provide date']);
        }

        if ($request->has('area_id')) { 
            $area_id = $request->input('area_id');
        } else {
            return response()->json(['response' => 'Please provide area_id']);
        }

        if ($request->has('bookingstatus')) { 
            $bookingstatus = $request->input('bookingstatus');
        } else {
            //default
            $bookingstatus = "all";
            //return response()->json(['response' => 'Please provide area_id']);
        }

        $arr = array();

        //Hämta area
        $area = DB::table('mrbs_area')
            ->select('mrbs_area.*')
            ->where('mrbs_area.id','=', $area_id)
            ->first();

        //Hämta rum
        $Rooms = DB::table('mrbs_room')
            ->whereIn('mrbs_room.area_id', [$area_id])
            //->where('mrbs_room.area_id', '=', '2')
            ->orderBy('sort_key')->get();

        //ex vis 08 - 20
        $startofday = strtotime(date($bookingdate)) + 60*60*$area->morningstarts;
        $endofday = strtotime(date($bookingdate)) + 60*60*$area->eveningends; 

        $periodresolution = 60*60;//entimmesperioder
        $fromtime = strtotime(date($bookingdate));
        $totime = $fromtime + 60*60*24 - 1; //idag 23:59
        $dayofweek = date('w', strtotime(date($bookingdate)));

        //Hämta alla bokningar för arean
        $bookingarray = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.*')
            ->where('mrbs_area.id', '=', $area_id)
            ->where('mrbs_entry.start_time', '<=', $totime)
            ->where('mrbs_entry.end_time', '>', $fromtime)
            ->get();
        //Skapa JSON
	    $json = "[ ";
	    //gå igenom alla rum
	    $roomcount = 0;
        foreach ($Rooms as $roomrow) {
            
            if($roomcount == 0) {
                $json .= "{";
                $roomcount = 1;
            } else {
                $json .= ",{";
            }
            $json .= "\"date\": \"$bookingdate\",";
            $json .= "\"weekday\": \"$dayofweek\",";
            $json .= "\"roomnumber\": \"" . $roomrow->room_number . "\",";
            $json .= "\"roomname\": \"" . $roomrow->room_name . "\",";
            $json .= "\"picture\": \"url\",";
            
            $json .= "\"bookings\": [";
            //hur många perioder är aktuell bokning? Ta hänsyn till DST(Daylight Saving Time)?
            //$n_slots = intval((($end_t - $start_t) - cross_dst($start_t, $end_t))/$resolution) + 1;
            $bookingcount = 0;
            for ($slottime = $startofday;$slottime <= $endofday;$slottime += $periodresolution) {
                $slotfree = true;
				$busyjson = "";
				if(isset($bookingarray)){
                    foreach($bookingarray as $row) {
                        if($row->room_id == $roomrow->id) {
                            if($row->start_time <= $slottime && $row->end_time > $slottime) {
                                $slotfree = false;
                                $start_t = max($this->round_t_down($row->start_time, $periodresolution, $startofday), $startofday);
                                $end_t = min($this->round_t_up($row->end_time, $periodresolution, $startofday) - $periodresolution, $endofday);
                                $numberofslots = intval(($end_t - $start_t)/$periodresolution) + 1;
                                for($i=0;$i<$numberofslots;$i++) {
                                    if ($i > 0) {
                                        $busyjson .= ",";
                                        $slottime += $periodresolution;
                                    }
                                    $today = date("Y-m-d H:i:s");
                                    $todayinseconds = strtotime(date($today));
                                    //4 = prel, 0 = conf
                                    if ($row->status == 4 && $todayinseconds > $row->start_time -15*60 && $todayinseconds < $row->start_time +15*60) {
                                        $busyjson .= "{\"hour\": \"" . date('H', $slottime)  . "\", \"status\": \"busy_confirm\", \"bookingid\": \"" . $row->id . "\", \"endtime\" : \"" . $end_t  ."\"}";
                                    } else {
                                        if ($row->status == 0) {
                                            if($row->type == "C") {
                                                $busyjson .= "{\"hour\": \"" . date('H', $slottime)  . "\", \"status\": \"closed\", \"bookingid\": \"" . $row->id . "\", \"endtime\" : \"" . $end_t  ."\"}";
                                            } else {
                                                $busyjson .= "{\"hour\": \"" . date('H', $slottime)  . "\", \"status\": \"busy\", \"bookingid\": \"" . $row->id . "\", \"endtime\" : \"" . $end_t  ."\"}";
                                            }
                                        } else {
                                            $busyjson .= "{\"hour\": \"" . date('H', $slottime)  . "\", \"status\": \"busy_tentative\", \"bookingid\": \"" . $row->id . "\", \"endtime\" : \"" . $end_t  ."\"}";
                                        }
                                    }
                                }
                                
                                if ($numberofslots == 1 && $i = 0 && $slottime < $endofday) {
                                    $busyjson .= ",";
                                }
                            }
                        }
                    }
                }
                //ta inte med lediga om önskad status != "free"
                if ($bookingstatus != "free") {
                    if ($busyjson != "") {
                        if($bookingcount == 0) {
                            $json .= "";
                            $bookingcount = 1;
                        } else {
                            $json .= ",";
                        }
                        $json .= $busyjson;
                    }
                }

                //ta bara med lediga om önskad status == "free" eller "all"
                if ($bookingstatus == "free" || $bookingstatus == "all") {
                    if ($slotfree) {
                        if($bookingcount == 0) {
                            $json .= "";
                            $bookingcount = 1;
                        } else {
                            $json .= ",";
                        }
                        $freendtime = $slottime + $periodresolution;
                        $json .= "{\"hour\": \"" . date('H', $slottime)  . "\", \"status\": \"free\", \"bookingid\": \"\", \"endtime\" : \"" . $freendtime ."\"}";
                    }
                }	
            }
            $json .= "]";//slut bookings
		    $json .= "}"; //slut rooms

        }
        $json .= "]"; //slut
        //gör om json till array(Laravels return skapar json av array)
        return response()->json(json_decode($json, true));
    }

    public function getBookings(Request $request)
    {
        $json = '[
        {
            "room": "Al-Khwarizmi",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Leibniz",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Pascal",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Scheele",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Leopold",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Agricola",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Bernoulli",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Dürer",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Galvani",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Watt",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Santorio",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Kovalevsky",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Ekeblad",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Sundström",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        },
        {
            "room": "Hammarström",
            "hour_08": "free",
            "hour_09": "busy",
            "hour_10": "closed",
            "hour_11": "free",
            "hour_12": "free",
            "hour_13": "busy",
            "hour_14": "closed",
            "hour_15": "free",
            "hour_16": "free",
            "hour_17": "busy",
            "hour_18": "closed",
            "hour_19": "free",
            "hour_20": "busy"
        }]';
        //gör om json till array(Laravels return skapar json av array)
        return response()->json(json_decode($json, true));
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

    // Round time down to the nearest resolution
    private function round_t_down($t, $resolution, $am7)
    {
        return (int)$t - (int)abs(((int)$t-(int)$am7) % $resolution);
    }


    // Round time up to the nearest resolution
    private function round_t_up($t, $resolution, $am7)
    {
    if (($t-$am7) % $resolution != 0)
    {
        return $t + $resolution - abs(((int)$t-(int)$am7) % $resolution);
    }
    else
    {
        return $t;
    }
    }
}
?>