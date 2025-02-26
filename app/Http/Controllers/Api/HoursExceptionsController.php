<?php

namespace App\Http\Controllers\Api;
use App\Models\HoursException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class HoursExceptionsController extends Controller
{
    public function get(Request $request) {
        $from =  date('Y-m-d H:i:s',strtotime($request->from));
        $to = date('Y-m-d H:i:s',strtotime($request->to));

        DB::statement('USE libapps');
        $exceptions = HoursException::where('started_at' , '<=', $to)
            ->where('ended_at', '>=', $from)
            ->get();
        return $exceptions;
    }
}