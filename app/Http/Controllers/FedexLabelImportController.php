<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FedexLabelImportController extends Controller
{
    public function __invoke(Request $request)
    {
        $expected = (string) config('services.fedex_label_upload.token');
        abort_unless(strlen($expected) >= 64, 503, 'Label upload is not configured.');
        abort_unless(hash_equals($expected, (string) $request->bearerToken()), 401);

        $data = $request->validate([
            'tracking_number' => ['required', 'string', 'regex:/\A[0-9]{10,30}\z/'],
            'pdf_base64' => ['required', 'string', 'max:13981016'],
        ]);

        $content = base64_decode($data['pdf_base64'], true);
        abort_if($content === false || strlen($content) === 0, 422, 'Invalid PDF data.');
        abort_if(strlen($content) > 10 * 1024 * 1024, 413, 'PDF exceeds 10 MB.');
        // Basic format screening; FPDI still validates/parses the PDF when printing.
        abort_if(strpos(substr($content, 0, 1024), '%PDF-') === false, 422, 'Missing PDF header.');

        $tracking = $data['tracking_number'];
        $path = "fedexlabels/{$tracking}.pdf";
        $saved = Storage::disk('local')->put($path, $content);
        abort_unless($saved, 500, 'Could not save label.');

        return response()->json([
            'saved' => true,
            'tracking_number' => $tracking,
        ]);
    }
}
