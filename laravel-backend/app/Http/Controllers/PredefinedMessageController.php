<?php

namespace App\Http\Controllers;

use App\Models\PredefinedMessage;
use Illuminate\Http\Request;

class PredefinedMessageController extends Controller
{
    // Fetch all predefined messages
    public function index()
    {
        return response()->json(PredefinedMessage::all(), 200);
    }

    // Store a new predefined message
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $message = PredefinedMessage::create([
            'message' => $request->input('message'),
        ]);

        return response()->json(['success' => true, 'data' => $message], 201);
    }

    // Delete a predefined message
    public function destroy($id)
    {
        $message = PredefinedMessage::find($id);

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $message->delete();

        return response()->json(['success' => true, 'message' => 'Predefined message deleted successfully'], 200);
    }
}
