<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketReplyResource;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Requests\TicketReplyStoreRequest;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Ticket::query();

            $query->orderBy('created_at', 'desc');

            if ($request->search) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%');
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->priority) {
                $query->where('priority', $request->priority);
            }

            // filter by user id (ticket owner)
            if (Auth::user()->role == 'user') {
                $query->where('user_id', Auth::user()->id);
            }

            $tickets = $query->get();

            return response()->json([
                'message' => 'Success to get Ticket',
                'data' => TicketResource::collection($tickets) // gunakan ::collection jika data lebih dari 1
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get Ticket',
                'data' => null
            ], 500);
        }
    }

    public function show($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Ticket Not Found'
                ], 404);
            }

            // validasi jika user mencoba mengakses ticket yang bukan miliknya
            if (Auth::user()->role == 'user' && $ticket->user_id != Auth::user()->id) {
                return response()->json([
                    'message' => 'You are not allowed to access this Ticket!'
                ], 403);
            }

            return response()->json([
                'message' => 'Success to get Ticket',
                'data' => new TicketResource($ticket)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get Ticket',
                'error' => $e->getMessage(  )
            ], 500);
        }
    }

    public function store(TicketStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = New Ticket();
            $ticket->user_id = Auth::user()->id;
            $ticket->code = 'TIC-' . rand(10000, 99999);
            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];
            $ticket->save();

            DB::commit();

            return response()->json([
                'message' => 'Ticket Created',
                'data' => new TicketResource($ticket)
            ], 201);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Create Ticket Failed',
                'data' => null
            ], 500);
        }
    }

    public function storeReply(TicketReplyStoreRequest $request, $code)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Ticket Not Found',
                ], 404);
            }

            // validasi jika user mencoba mengakses ticket yang bukan miliknya
            if (Auth::user()->role == 'user' && $ticket->user_id != Auth::user()->id) {
                return response()->json([
                    'message' => 'You are not allowed to reply this Ticket!'
                ], 403);
            }

            $ticketReply = New TicketReply();
            $ticketReply->ticket_id = $ticket->id;
            $ticketReply->user_id = Auth::user()->id;
            $ticketReply->content = $data['content'];
            $ticketReply->save();

            if (Auth::user()->role == 'admin') {
                $ticket->status = $data['status'];

                if ($ticket->status == 'resolved') {
                    $ticket->completed_at = now();
                }
                $ticket->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Reply Created',
                'data' => new TicketReplyResource($ticketReply)
            ], 201);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Create Reply Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
