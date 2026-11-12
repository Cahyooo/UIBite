<?php

function render_flashes(): void
{
    $flashes = consume_flashes();
    if (!$flashes) return;
    echo '<div class="container-app space-y-2 pt-4">';
    foreach ($flashes as $item) {
        $style = match ($item['type']) {
            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
            default => 'border-rose-200 bg-rose-50 text-rose-800',
        };
        echo '<div class="toast flex items-start gap-3 rounded-xl border px-4 py-3 text-sm ' . $style . '"><i data-lucide="' . ($item['type'] === 'success' ? 'check-circle-2' : 'alert-circle') . '" class="mt-0.5 h-4 w-4 shrink-0"></i><p class="flex-1">' . e($item['message']) . '</p><button type="button" onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100"><i data-lucide="x" class="h-4 w-4"></i></button></div>';
    }
    echo '</div>';
}
