<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrackOrderController extends Controller
{
    public function create(): View
    {
        return view('orders.track', ['order' => null]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'whatsapp_number' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s()]+$/'],
        ], [
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda + - ( ).',
        ]);

        $order = CustomOrder::find($validated['order_id']);

        if (! $order || ! $order->matchesPhone($validated['whatsapp_number'])) {
            return back()->withErrors([
                'order_id' => 'Nomor order atau nomor WhatsApp tidak cocok.',
            ])->onlyInput('order_id');
        }

        return view('orders.track', compact('order'));
    }

    /** Halaman lacak berbasis token (dikirim via WhatsApp; token 40 karakter acak). */
    public function token(string $token): View
    {
        $order = CustomOrder::where('tracking_token', $token)->firstOrFail();

        return view('orders.track', compact('order'));
    }

    public function downloadDesign(string $token): StreamedResponse
    {
        $order = CustomOrder::where('tracking_token', $token)->firstOrFail();

        abort_unless($order->design_file_admin, 404);
        abort_unless(Storage::disk('local')->exists($order->design_file_admin), 404);

        return Storage::disk('local')->download(
            $order->design_file_admin,
            'desain-'.$order->id.'-'.basename($order->design_file_admin),
        );
    }

    public function approve(Request $request, string $token): RedirectResponse
    {
        $order = CustomOrder::where('tracking_token', $token)->firstOrFail();

        if (! in_array($order->status, [CustomOrder::STATUS_DESIGN, CustomOrder::STATUS_READY], true)) {
            return redirect()
                ->route('track.token', $token)
                ->with('track_error', 'Persetujuan desain baru bisa diberikan pada tahap "'.CustomOrder::STATUS_LABELS[CustomOrder::STATUS_DESIGN].'" / "'.CustomOrder::STATUS_LABELS[CustomOrder::STATUS_READY].'".');
        }

        if ($order->design_approved_at !== null) {
            return redirect()
                ->route('track.token', $token)
                ->with('track_flash', 'Terima kasih, desain sudah pernah Anda setujui.');
        }

        $order->forceFill(['design_approved_at' => now()])->save();

        return redirect()
            ->route('track.token', $token)
            ->with('track_flash', 'Terima kasih! Persetujuan desain Anda sudah tercatat.');
    }

    public function revision(Request $request, string $token): RedirectResponse
    {
        $order = CustomOrder::where('tracking_token', $token)->firstOrFail();

        $validated = $request->validate([
            'revision_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'revision_reason.min' => 'Alasan revisi minimal 5 karakter.',
        ]);

        if (! in_array($order->status, [CustomOrder::STATUS_DESIGN, CustomOrder::STATUS_READY], true)) {
            return redirect()
                ->route('track.token', $token)
                ->with('track_error', 'Revisi desain hanya bisa diminta pada tahap "'.CustomOrder::STATUS_LABELS[CustomOrder::STATUS_DESIGN].'" / "'.CustomOrder::STATUS_LABELS[CustomOrder::STATUS_READY].'". Silakan hubungi kami via WhatsApp.');
        }

        $from = $order->status;

        $order->forceFill([
            'status' => CustomOrder::STATUS_DESIGN,
            'design_approved_at' => null,
            'design_revisions' => $order->design_revisions + 1,
            'design_revision_reason' => $validated['revision_reason'],
        ])->save();

        if ($from !== CustomOrder::STATUS_DESIGN) {
            $order->statusHistory()->create([
                'from_status' => $from,
                'to_status' => CustomOrder::STATUS_DESIGN,
                'changed_by' => null,
            ]);
        }

        return redirect()
            ->route('track.token', $token)
            ->with('track_flash', 'Permintaan revisi ke-'.$order->design_revisions.' sudah tercatat. Desain akan kami revisi secepatnya.');
    }
}
