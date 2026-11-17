<?php

namespace App\Services;

use App\Models\GstSetting;

class GstService
{
    /**
     * Settings already loaded during the current request/scope.
     */
    private ?GstSetting $settings = null;

    public function settings(): GstSetting
    {
        return $this->settings ??= GstSetting::current();
    }

    public function isRegistered(): bool
    {
        return (bool) $this->settings()->gst_registered;
    }

    /**
     * The rate that should actually be applied. Unregistered businesses
     * charge no GST even if a rate is still stored for future use.
     */
    public function rate(): float
    {
        return $this->isRegistered() ? (float) $this->settings()->gst_rate : 0.0;
    }

    /**
     * Whether supplier/item prices are entered GST-inclusive.
     */
    public function pricesIncludeGst(): bool
    {
        return (bool) $this->settings()->prices_include_gst;
    }

    public function multiplier(): float
    {
        return 1 + ($this->rate() / 100);
    }

    /**
     * Convert an entered price to a GST-exclusive amount.
     */
    public function amountExcludingGst(float|int|string|null $amount): float
    {
        $amount = (float) $amount;

        if ($this->rate() <= 0 || ! $this->pricesIncludeGst()) {
            return $amount;
        }

        return $amount / $this->multiplier();
    }

    /**
     * GST contained in a GST-exclusive amount.
     */
    public function gstOn(float|int|string|null $exclusiveAmount): float
    {
        // Multiply before dividing so common amounts (e.g. $1.50 at 15%)
        // round to the expected cent instead of 0.224999…
        return (float) $exclusiveAmount * $this->rate() / 100;
    }

    /**
     * GST-inclusive value for a GST-exclusive amount.
     */
    public function amountIncludingGst(float|int|string|null $exclusiveAmount): float
    {
        return (float) $exclusiveAmount + $this->gstOn($exclusiveAmount);
    }

    /**
     * The GST context exposed by cost/pricing APIs.
     *
     * @return array<string, bool|float>
     */
    public function context(): array
    {
        return [
            'gst_registered' => $this->isRegistered(),
            'gst_rate' => $this->rate(),
            'prices_include_gst' => $this->pricesIncludeGst(),
        ];
    }

    /**
     * Forget the cached settings after an update.
     */
    public function forget(): void
    {
        $this->settings = null;
    }
}
