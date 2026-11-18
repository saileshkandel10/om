<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NoticeResource;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NoticeController extends Controller
{
    /**
     * List published notices, optionally filtered by type.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Notice::query();

        if ($request->filled('notice_type')) {
            $query->where('notice_type', $request->string('notice_type'));
        }

        if ($request->filled('audience_type')) {
            $query->where(function ($q) use ($request) {
                $q->where('audience_type', $request->string('audience_type'))
                    ->orWhere('audience_type', 'all');
            });
        }

        return NoticeResource::collection(
            $query->latest()->paginate($request->integer('per_page', 20))
        );
    }

    /**
     * Show a single notice.
     */
    public function show(Notice $notice): NoticeResource
    {
        return new NoticeResource($notice);
    }
}
