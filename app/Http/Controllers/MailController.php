<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function send(Request $request)
    {
        // Lấy thông tin người dùng đã đăng nhập
        $user = auth()->user();

        // Gửi email cho người dùng hiện tại
        Mail::to(auth()->user()->email)->send(new sendmailNotification($user));

        return redirect("/")->with('success', 'Email đã được gửi thành công!');
    }
}
