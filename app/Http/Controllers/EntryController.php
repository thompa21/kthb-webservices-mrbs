<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Entry;
use Illuminate\Support\Facades\View;  
use DB; 
//use Auth;

//TODO Validera inkommande data för create/update/delete
class EntryController extends Controller
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
            'index', 'getEntry', 'createEntry', 'updateEntry','deleteEntry', 'search'
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
        $query = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.id', 'mrbs_entry.name', 'mrbs_entry.start_time', 'mrbs_entry.end_time', 'mrbs_entry.status', 'mrbs_entry.type', 'mrbs_room.room_name', 'mrbs_room.area_id', 'mrbs_area.default_view');
        
        if($request->input('fromDate')){
            $fromDate = $request->input('fromDate');
            $query = $query->when($fromDate, function($q) use ($fromDate) {
                return $q->where('start_time', '>=', $fromDate);
            });
        }

        if($request->input('toDate')){
            $toDate = $request->input('toDate');
            $query = $query->when($toDate, function($q) use ($toDate) {
                return $q->where('end_time', '<=', $toDate);
            });
        }

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
            return response()->json($query->orderBy('room_name')->orderBy('start_time')->take($limit)->get());
        } else {
            //returnera endast alla om parameter limit = none. Men med paginering
            return response()->json($query->orderBy('room_name')->orderBy('start_time')->paginate(100));
        }
    }

    /*************************************************
     *  
     * Funktion som kan anropas utan några nycklar, 
     * se till att det inte finns några känsliga data
     * som returneras!
     * 
    *************************************************/
    public function noauthgetEntry($id)
    {
        $entry = Entry::find($id);

        if ( ! $entry)
        {
            return response()->json(['response' => trans('messages.bookingnotfound')]);
        }

        $entrywithroomname = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.id', 'mrbs_entry.name', 'mrbs_entry.start_time', 'mrbs_entry.end_time', 'mrbs_entry.status', 'mrbs_entry.type', 'mrbs_room.room_name', 'mrbs_room.area_id', 'mrbs_area.default_view')
            ->where('mrbs_entry.id', '=', $entry->id)
            ->first();
        return response()->json($entrywithroomname);
    }

    public function index(Request $request)
    {
        $query = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.*', 'mrbs_room.room_name', 'mrbs_room.area_id', 'mrbs_area.default_view');
        
        if($request->input('fromDate')){
            $fromDate = $request->input('fromDate');
            $query = $query->when($fromDate, function($q) use ($fromDate) {
                return $q->where('start_time', '>=', $fromDate);
            });
        }

        if($request->input('toDate')){
            $toDate = $request->input('toDate');
            $query = $query->when($toDate, function($q) use ($toDate) {
                return $q->where('end_time', '<=', $toDate);
            });
        }

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
            return response()->json($query->orderBy('room_name')->orderBy('start_time')->take($limit)->get());
        } else {
            //returnera endast alla om parameter limit = none. Men med paginering
            return response()->json($query->orderBy('room_name')->orderBy('start_time')->paginate(100));
        }
    }

    public function getEntry($id)
    {
        $entry = Entry::find($id);

        if ( ! $entry)
        {
            return response()->json(['response' => trans('messages.bookingnotfound')]);
            //test av kvitteringssida
            //return View::make('confirmationmessage', array('name' => 'Leibniz','start_time' => '1466920800', 'end_time' => '1466924400', 'area_id' => '2', 'view' => 'day'));
        }

        $entrywithroomname = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.*', 'mrbs_room.room_name', 'mrbs_room.area_id', 'mrbs_area.default_view')
            ->where('mrbs_entry.id', '=', $entry->id)
            ->first();
        return response()->json($entrywithroomname);
    }

    /*
        Funktion som kvitterar en preliminär bokning utifrån den token som satts på bokningen
        preliminär: status = 4
        kvitterad: status = 0
    */
    public function confirm($confirmation_code)
    {
        $confirmation = false;

        if( ! $confirmation_code)
        {
            return View::make('confirmationmessage', array('message' => trans('messages.confirmcodemissing'), 'confirmation' => $confirmation, 'name' => '', 'start_time' => '', 'end_time' => '', 'area_id' => '', 'view' => 'day'));
        }

        $entry = Entry::where('confirmation_code', $confirmation_code)->first();

        if ( ! $entry)
        {
            return View::make('confirmationmessage', array('message' => trans('messages.confirmnotfound'), 'confirmation' => $confirmation, 'name' => '', 'start_time' => '', 'end_time' => '', 'area_id' => '', 'view' => 'day'));
        }

        $today = date("Y-m-d H:i:s");
        $todayinseconds = strtotime(date($today));
        $slotinseconds =  $entry->start_time;
        //Kvittera bara om inom intervallet 15 min före/efter start_time
		if ($todayinseconds >= $slotinseconds - 15 * 60 AND $todayinseconds <= $slotinseconds + 15 * 60 ) {
            $entry->status = 0;
            $entry->confirmation_code = null;
            $entry->save();
        } else {
            return View::make('confirmationmessage', array('message' => trans('messages.notinconfirmperiod'), 'confirmation' => $confirmation, 'name' => '', 'start_time' => '', 'end_time' => '', 'area_id' => '', 'view' => 'day'));
        }
        
        
        $entrywithroomname = DB::table('mrbs_entry')
            ->join('mrbs_room', 'mrbs_entry.room_id', '=', 'mrbs_room.id')
            ->join('mrbs_area', 'mrbs_room.area_id', '=', 'mrbs_area.id')
            ->select('mrbs_entry.*', 'mrbs_room.room_name', 'mrbs_room.area_id', 'mrbs_area.default_view')
            ->where('mrbs_entry.id', '=', $entry->id)
            ->first();
        
        //default_view: 0 = day, 1 = week
        $view = "day";
        if ($entrywithroomname->default_view == 0) {
            $view = "day";
        } else {
            $view = "week";
        }
        return View::make('confirmationmessage', array('message' => '', 'confirmation' => $confirmation, 'name' => $entrywithroomname->room_name, 'start_time' => $entrywithroomname->start_time, 'end_time' => $entrywithroomname->end_time, 'area_id' => $entrywithroomname->area_id, 'view' => $view));
    }

    public function createEntry(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'create_by' => 'required|email',
            'start_time' => 'required',
            'end_time' => 'required',
            'entry_type' => 'required',
            'room_id' => 'required',
            'modified_by' => 'required',
            'type' => 'required',
            'status' => 'required',
            'lang' => 'required'
        ]);

        $entry = Entry::create($request->all());
        //201 = http-statuskod för att nåt skapats.
        return response()->json($entry, 201);
    }

    public function updateEntry(Request $request, $id)
    {
        $entryuser = Entry::findOrFail($id);
        $entry->name = $request->input('name');
        $entry->create_by = $request->input('create_by');
        //return response()->json($entry, 200);
        $entry->save();
        return response()->json($entry);   
    }

    public function deleteEntry($id){
        $entry = Entry::findOrFail($id);
        $entry->delete();
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
        if ($request->has('create_by')) {   
            $entry = Entry::where('create_by', $request->input('create_by'))
            ->orderBy('start_time', 'desc')
            ->take($limit)
            ->get();
        }
        return response()->json($entry);
    }
}
?>