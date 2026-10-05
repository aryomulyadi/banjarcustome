<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject('Pesanan Baru #ord-'.$order->id.' — '.$order->name)
            ->line('Ada pesanan baru masuk dari website.')
            ->line('Nama: '.$order->name)
            ->line('WhatsApp: '.$order->whatsapp_number);

        if ($order->product) {
            $mail->line('Produk: '.$order->product->title);
        }

        if ($order->service_type) {
            $mail->line('Jenis: '.$order->service_type);
        }

        if ($order->quantity) {
            $mail->line('Jumlah: '.$order->quantity.' pcs');
        }

        if ($order->deadline) {
            $mail->line('Deadline: '.$order->deadline->translatedFormat('d F Y'));
        }

        $mail->line('Estimasi: '.($order->is_express ? 'EXPRESS (same-day/di bawah 10 hari)' : 'Reguler'));

        if ($order->delivery_method) {
            $mail->line('Pengiriman: '.($order->delivery_method === 'ambil' ? 'Ambil sendiri' : 'Dikirim'));
        }

        if ($order->order_details) {
            $mail->line('Detail: '.mb_strimwidth($order->order_details, 0, 200, '…'));
        }

        return $mail
            ->action('Lihat Pesanan', route('admin.orders.show', $order))
            ->line('Link ini hanya bisa dibuka oleh admin yang login.');
    }

    /**
     * Daftar penerima: semua akun admin di DB + BC_ADMIN_EMAIL (bisa diubah kapan saja).
     *
     * @return array<int, string>
     */
    public static function recipients(): array
    {
        $emails = User::where('role', 'admin')
            ->pluck('email')
            ->all();

        $configured = (string) config('banjarcustom.admin_email');

        if ($configured !== '') {
            $emails[] = $configured;
        }

        return array_values(array_unique(array_filter($emails, fn ($email) => $email !== '' && $email !== null)));
    }
}
