<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RewardImageController extends Controller
{
    public function update(Request $request, RewardCatalog $reward)
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        $path = $request->file('image')->store('rewards', 'public');

        if ($reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        $reward->update([
            'image_path' => $path,
            'thumbnail_path' => $path,
        ]);

        return back()->with('success', 'Đã cập nhật ảnh reward.');
    }
}
