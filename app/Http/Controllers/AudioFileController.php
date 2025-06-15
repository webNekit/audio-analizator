<?php

namespace App\Http\Controllers;

use App\Models\AudioFile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AudioFileController extends Controller
{
    use AuthorizesRequests;

    public function create()
    {
        return view('audio.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'audio' => 'required|mimes:mp3,wav,ogg|max:10240', // до 10 MB
        ]);

        $path = $request->file('audio')->store('audio_files', 'public');

        $file = AudioFile::create([
            'user_id' => auth()->id(),
            'file_name' => $request->file('audio')->getClientOriginalName(),
            'file_path' => $path,
            'analyzed_data' => null,
        ]);

        return redirect()->route('audio.show', $file)->with('success', 'Файл успешно загружен!');
    }

    public function show(AudioFile $audioFile)
    {
        return view('audio.show', compact('audioFile'));
    }
}
