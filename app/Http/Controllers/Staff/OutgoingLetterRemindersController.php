<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LetterReminder;
use App\Models\OutgoingLetter;
use Illuminate\Http\Request;

class OutgoingLetterRemindersController extends Controller
{
    public function store(Request $request, OutgoingLetter $letter)
    {
        $this->authorize('create', [LetterReminder::class, $letter]);

        $validData = request()->validate([
            'attachments' => 'required|array|min:1|max:2',
            'attachments.*' => 'file|max:200|mimes:jpeg,jpg,png,pdf',
        ]);

        $letter_reminder = $letter->reminders()->create($validData);

        $letter_reminder->attachments()->createMany(
            array_map(static function ($attachedFile) {
                return [
                    'original_name' => $attachedFile->getClientOriginalName(),
                    'path' => $attachedFile->store('/letter_attachments/outgoing/reminders'),
                ];
            }, $request->file('attachments'))
        );

        return redirect()->back();
    }
}
