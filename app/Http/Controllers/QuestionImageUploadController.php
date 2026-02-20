<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionImageUploadController extends Controller
{
    /**
     * Store an uploaded question-bank image and return its public URL.
     *
     * POST /admin/question-image-upload
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'], // 5 MB max
        ]);

        $file = $request->file('image');

        // Build a unique, safe filename: timestamp + random + original extension
        $filename = now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

        // Store in storage/app/public/question-images  →  accessible via /storage/question-images/...
        $file->storeAs('question-images', $filename, 'public');

        return response()->json([
            'url' => asset('storage/question-images/' . $filename),
        ]);
    }
}
