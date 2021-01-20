<?php

namespace Igniter\Local\Listeners;

use Admin\Models\Orders_model;
use Admin\Models\Reviews_model;
use Carbon\Carbon;
use Igniter\Flame\Traits\EventEmitter;
use Illuminate\Contracts\Events\Dispatcher;

class ChaseReviews
{
    use EventEmitter;

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('automation.order.schedule.hourly', __CLASS__.'@runChase');
    }

    public function runChase($order)
    {
        if (!(bool)ReviewSettings::get('chase_reviews', false))
            return;
            
        $chaseStart = Carbon::now()->startOfHour()->subHours((int)ReviewSettings::get('chase_reviews_after', 24));
        $chaseEnd = $chaseStart->copy()->addHour();

        if (!$order->between($chaseStart, $chaseEnd))
            return;

        $reviews = Reviews_model::where([
            'sale_type' => 'orders',
            'sale_id' => $order->order_id,
        ])->get();

        if ($reviews->count() > 0)
            return;

        $order->mailSend('igniter.local::mail.review_chase', 'customer');                
    }
}
