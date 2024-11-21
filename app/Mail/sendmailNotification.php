<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $order;

    // Constructor nhận cả thông tin người dùng và đơn hàng
    public function __construct($user, $order)
    {
        $this->user = $user;
        $this->order = $order;
    }

    public function build()
    {
        // Tạo view email và truyền thông tin người dùng, đơn hàng
        return $this->view('mail.index')
                    ->with([
                        'user' => $this->user,
                        'order' => $this->order,
                    ]);
    }
}

