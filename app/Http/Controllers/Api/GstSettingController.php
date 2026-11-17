<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GstSettingRequest;
use App\Models\GstSetting;
use App\Services\GstService;
use Illuminate\Http\JsonResponse;

class GstSettingController extends Controller
{
    public function __construct(
        private readonly GstService $gstService
    ) {}

    /**
     * Display the business GST settings.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'message' => 'GST settings',
            'settings' => $this->toArray($this->gstService->settings()),
        ]);
    }

    /**
     * Update the business GST settings.
     */
    public function update(GstSettingRequest $request): JsonResponse
    {
        $settings = $this->gstService->settings();
        $settings->update($request->validated());

        // The service caches the row for the duration of the request.
        $this->gstService->forget();

        return response()->json([
            'message' => 'GST settings updated successfully',
            'settings' => $this->toArray($settings->fresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(GstSetting $settings): array
    {
        return [
            'gst_registered' => (bool) $settings->gst_registered,
            'gst_rate' => (float) $settings->gst_rate,
            'prices_include_gst' => (bool) $settings->prices_include_gst,
            'updated_at' => $settings->updated_at?->toIso8601String(),
        ];
    }
}
