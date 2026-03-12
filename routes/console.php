<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\OrderReminderService;
use App\Services\OrderPaymentLifecycleService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:send-return-reminders', function (OrderReminderService $reminderService) {
    $total = $reminderService->dispatchDueReturnReminders();
    $this->info("Reminder terkirim: {$total}");
})->purpose('Kirim reminder 6 jam & 3 jam sebelum rental berakhir');

Artisan::command('orders:expire-pending-payments', function (OrderPaymentLifecycleService $lifecycleService) {
    $expired = $lifecycleService->expirePendingPayments(1440);
    $this->info("Order pending yang expired: {$expired}");
})->purpose('Expire order pending payment yang melewati batas 24 jam');

Schedule::command('orders:send-return-reminders')
    ->everyThirtyMinutes()
    ->withoutOverlapping();

Schedule::command('orders:expire-pending-payments')
    ->everyTenMinutes()
    ->withoutOverlapping();
