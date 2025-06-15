<?php

namespace App\Http\Controllers;

use App\Models\AudioFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AudioAnalysisController extends Controller
{
    public function history()
    {
        $files = AudioFile::where('user_id', auth()->id())->latest()->paginate(10);
        return view('analysis.history', compact('files'));
    }

    public function show(AudioFile $audioFile)
    {
        return view('analysis.show', compact('audioFile'));
    }

    public function downloadReport(AudioFile $audioFile)
    {
        $reportPath = "reports/{$audioFile->id}.txt";

        if (Storage::exists($reportPath)) {
            return Storage::download($reportPath, "{$audioFile->file_name}_report.txt");
        }

        return back()->with('error', 'Отчет еще не сформирован.');
    }

    public function destroy(AudioFile $audioFile)
    {

        Storage::delete([$audioFile->file_path, "reports/{$audioFile->id}.txt"]);
        $audioFile->delete();

        return redirect()->route('analysis.history')->with('success', 'Анализ удален.');
    }
}
