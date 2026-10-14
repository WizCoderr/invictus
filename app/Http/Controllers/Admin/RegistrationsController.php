<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationsController extends Controller
{
    public function index(Request $request)
    {
        // A member's name makes a personal invite QR: their guests find "Invited by" already filled in.
        $invitedBy = is_string($name = $request->query('invited_by')) ? $name : '';
        $url = route('register', array_filter(['invited_by' => $invitedBy]));
        $slug = Str::slug($invitedBy);

        // Level M: a printed poster still scans with a scuff or a fold across it.
        $svg = (new Writer(new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd())))
            ->writeString($url, Encoder::DEFAULT_BYTE_MODE_ENCODING, ErrorCorrectionLevel::M());

        return view('admin.registrations', [
            'registrations' => Registration::with('businessCategory', 'chapter')->latest()->get(),
            'registerUrl' => $url,
            'invitedBy' => $invitedBy,
            'qrSrc' => 'data:image/svg+xml;base64,'.base64_encode($svg),
            'qrFile' => 'registration-qr'.($slug !== '' ? '-'.$slug : ''),
        ]);
    }
}
