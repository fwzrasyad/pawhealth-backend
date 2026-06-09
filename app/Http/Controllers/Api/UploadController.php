<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected $storageService;

    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * POST /api/upload/image
     * Upload an image to Firebase Storage and return the public URL.
     *
     * Accepts:
     *   - image: the file (required, max 5MB, image types only)
     *   - folder: the storage folder (required, e.g. 'users', 'pets', 'vets', 'journals')
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'folder' => 'required|string|in:users,pets,vets,journals,licenses',
        ]);

        $url = $this->storageService->upload($request->file('image'), $request->folder);

        return response()->json([
            'message' => 'Image uploaded successfully.',
            'url'     => $url,
        ]);
    }
}
