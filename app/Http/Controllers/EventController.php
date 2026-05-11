<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventRegistrationMail; // Import your Mailable

class EventController extends Controller
{
    public function getAllEvents()
    {
        $events = Event::select('id', 'event_title as title', 'event_description as description', 'event_start_date as start_date', 'event_end_date as end_date','type as type')
                        ->where('event_status', 'upcoming')
                        ->orderBy('event_start_date', 'asc')
                        ->get();

        return response()->json($events);
    }

    /**
     * UPDATED: Handle multiple event registrations and send emails
     */
    public function registerMultiple(Request $request)
    {
    $validated = $request->validate([
        'name'        => 'required|string|max:255',
        'email'       => 'required|email',
        'phone'       => 'required|digits:10',
        'institution' => 'nullable|string',
        'event_ids'   => 'required|array',
        'event_ids.*' => 'exists:events,id',
    ]);

    try {
        DB::beginTransaction();

        $now = now();
        $registrations = [];
        
        // Fetch all selected event details at once
        $selectedEvents = Event::whereIn('id', $validated['event_ids'])->get();

        foreach ($selectedEvents as $event) {
            $registrations[] = [
                'event_id'          => $event->id,
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'phone'             => $validated['phone'],
                'institution'       => $validated['institution'],
                'attendance_status' => 'registered',
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        // 1. Bulk insert into database
        DB::table('event_registrations')->insert($registrations);

        // 2. SEND SINGLE EMAIL with all events
        $userData = (object)[
            'name' => $validated['name'],
            'email' => $validated['email']
        ];

        // Pass the whole $selectedEvents collection
        Mail::to($validated['email'])->send(new EventRegistrationMail($selectedEvents, $userData));

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully registered for all events!',
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' =>  $e->getMessage(),
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    public function getLatestEvent()
    {
        $event = Event::latest()->first();
        if (!$event) return response()->json(['message' => 'No events found'], 404);

        return response()->json([
            'id' => $event->id,
            'title' => $event->event_title,
            'description' => $event->event_description,
        ]);
    }
}