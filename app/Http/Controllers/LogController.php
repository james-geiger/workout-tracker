<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogRequest;
use App\Http\Requests\UpdateLogRequest;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Modifier;
use App\Models\Exercise;
use App\Models\Unit;
use App\Models\Type;

use Illuminate\Support\Facades\Log as Logger;

use Inertia\Inertia;

use App\Http\Controllers\WorkoutController;

use Auth;

class LogController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreLogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLogRequest $request)
    {
        // Retrieve the validated input data
        $validated = $request;

        $exercise = Exercise::find($request->exercise_id);

        $log = Log::create([
            'exercise_id' => $exercise->id,
            'workout_id' => $request->workout_id,
            'type_id' => $exercise->type_id,
            'order' => $request->order,
            'user_id' => Auth::user()->id
        ]);

        return redirect()->action([LogController::class, 'show'], $log->id)->with('status', 'log-created');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Log  $Log
     * @return \Illuminate\Http\Response
     */
    public function show(Log $log)
    {

        $units = Unit::all();
        $types = Type::all();

        $log->loadCount('sets');
        $log->loadSum('sets', 'reps');

        $modifiers = Modifier::all();

        $workout = $log->workout;

        $last_log = Log::with('workout')
                        ->where('exercise_id', $log->exercise->id)
                        ->where('id', '<>', $log->id)
                        ->select('logs.*', \DB::raw('(SELECT date FROM workouts WHERE logs.workout_id = workouts.id ) as date'))
                        ->orderBy('date', 'desc')
                        ->withCount('sets')
                        ->withSum('sets', 'reps')
                        ->limit(1)
                        ->get();

        return Inertia::render('Log/Show', ['workout' => $workout, 'log' => $log, 'last_log' => $last_log, 'modifiers' => $modifiers, 'units' => $units, 'types' => $types]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Log  $Log
     * @return \Illuminate\Http\Response
     */
    public function edit(Log $Log)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLogRequest  $request
     * @param  \App\Models\Log  $Log
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLogRequest $request, Log $log)
    {
        $log->modifiers()->toggle($request->modifier_id);

        return back()->with('status', 'log-modifier-updated');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request)
    {
        $logs = $request->reordered_logs;

        foreach ($logs as $key => $log) {
            $l = Log::find($log);
            $l->order = $key + 1;
            $l->save();
        }

        return back()->with('status', 'log-modifier-updated');
    }

    public function updateType(Request $request, Log $log)
    {
        $log->type_id = $request->type['id'];

        $log->save();

        $log->sets()->delete();

        return redirect()->action([LogController::class, 'show'], $log->id)->with('status', 'log-type-updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Log  $Log
     * @return \Illuminate\Http\Response
     */
    public function destroy(Log $log)
    {
        $log->sets()->delete();
        $log->delete();

        return redirect()->action([WorkoutController::class, 'show'], $log->workout_id)->with('status', 'log-destroyed');

    }
}
