<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    public function send(Request $request){
        if ($request->hasHeader('X-APP-KEY')) {
            $key = $request->header('X-APP-KEY', null);
            $receiver = $request->header('X-APP-RECEIVER');

            if(!$receiver){
                return response()->json(['message' => 'No receiver specified!'],404);
            }

            if ($key === 'zStyDjwIXspS1C') {
                // validate request
                $validated = $request->validate([
                    'name'    => 'required|string|max:100',
                    'email'   => 'required|email|max:64',
                    'subject' => 'required|string|max:64',
                    'message' => 'required|string|max:255',
                ]);

                // send email
                Mail::send([], [], function ($mail) use ($validated, $receiver) {
                    $mail->to($receiver)
                        ->from($validated['email'], $validated['name'])
                        ->subject($validated['subject'])
                        ->html(
                            "<p><strong>Hi {$receiver}</strong>,</p>
                            <p style=\"margin-bottom:20px;\">Someone is contacting you from your portfolio website;</p>
                            <p><strong>Name:</strong> {$validated['name']}</p>
                            <p><strong>Email:</strong> {$validated['email']}</p>
                            <p><strong>Message:</strong><br>{$validated['message']}</p>"
                        );
                });

                return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
