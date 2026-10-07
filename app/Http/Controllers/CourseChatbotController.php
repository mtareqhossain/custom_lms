<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Course;
use App\Services\CourseChatbot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseChatbotController extends Controller
{
    public function ask(Request $request, Course $course, CourseChatbot $chatbot)
    {
        $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $message = $chatbot->ask(Auth::user(), $course, $request->input('question'));
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong answering that — please try again shortly.'], 500);
        }

        return response()->json([
            'question' => $message->question,
            'answer' => $message->answer,
        ]);
    }

    public function history(Course $course)
    {
        $messages = ChatMessage::where('course_id', $course->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(20)
            ->get(['question', 'answer', 'created_at'])
            ->reverse()
            ->values();

        return response()->json($messages);
    }
}
