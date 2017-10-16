<?php

namespace App\Listeners;

use App\Events\KitWasWeighed;
use App\Events\LitterWasWeighed;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManageLitterWeights
{
    /**
     * @var Event
     */
    private $event;

    /**
     * Create the event listener.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Handle the event.
     *
     * @param  KitWasWeighed $event
     * @return void
     */
    public function byKitWeight(KitWasWeighed $event)
    {
        $litter = $event->kit->litter;
        $litter->updateWeights();
        $litter->update();
    }

    public function byLitterWeight(LitterWasWeighed $event)
    {
        $event->litter->updateWeights();
        $event->litter->update();

        $weighs = $event->litter->weighs()->count();
        $plannedEvent = null;
        if($weighs = $event->litter->weighs()->count() == 0){    //temporary fix for issue #127 - wrong subtype on first weigh task
            $plannedEvent = $event->litter->events()
                                ->whereNotNull('breed_id')
                                ->where('icon', 'fa-balance-scale bg-yellow first-weight')
                                ->first();
        }
        if(!$plannedEvent)
            $plannedEvent = $event->litter->events()->whereNotNull('breed_id')->where('subtype', 'weigh')->orderBy('date')->first();

        if ($plannedEvent) {
            $plannedEvent->date   = $event->date;
            $plannedEvent->closed = 1;
            $plannedEvent->update();
        } else {
            $plannedEvent = $event->litter->events()->where('type', 'litter')->where('subtype', 'weigh')->orderBy('date')->first();

            if ($plannedEvent != null)
                return;

            // dd($this->event,$plannedEvent);
            if($plannedEvent->recurring == 1)
            {
                $plannedEvent->closed = 1;
                $plannedEvent->save();
            } else {
                $recurringEvent = $plannedEvent->recurringEvents()->where('closed', false)->orderBy('date')->first();

                $recurringEvent->closed = 1;
                $recurringEvent->save();
            }

            // $this->event->type    = 'litter';
            // $this->event->name    = 'weigh' . ($weighs + 1);
            // $this->event->subtype = 'weigh';
            // $this->event->closed  = 1;
            // $this->event->recurring  = 1;
            // $this->event->holderName  = $event->litter->given_id;
            // $this->event->date    = $event->date;
            // $this->event->save();
            // $event->litter->events()->attach($this->event);
            // auth()->user()->events()->attach($this->event);

            // $plannedEvent = $event->litter->events()->where('type', 'litter')->where('subtype', 'weigh')->orderBy('date')->first();

            // $recurringEvent = $plannedEvent->recurringEvents()->where('closed', false)->orderBy('date')->first();
            

            // $this->event->type    = 'litter';
            // $this->event->name    = $plannedEvent->name;
            // $this->event->subtype = 'weigh';
            // $this->event->closed  = true;
            // $this->event->recurring  = $plannedEvent->recurring;
            // $this->event->holderName  = $plannedEvent->holderName;
            // $this->event->icon = $plannedEvent->icon;
            // $this->event->missed = $plannedEvent->missed;
            // $this->event->breed_id = $plannedEvent->breed_id;
            // $this->event->archived = $plannedEvent->archived;

            // $pluckDateRec = $plannedEvent->recurringEvents()->where('closed', false)->orderBy('date')->first();
            // $pluckDate = $pluckDateRec->date;
            // $this->event->date = $pluckDate;

            // $this->event->save();

            // $event->litter->events()->attach($this->event);
            // auth()->user()->events()->attach($this->event);

            // $recurringEvent->closed = true;
            // $recurringEvent->save();
            // $recurringEvent = $plannedEvent->recurringEvents()->where('closed', false)->orderBy('date')->first();
            // $plannedEvent->date = $recurringEvent->date;
            // $plannedEvent->save();

            // $recurringIds = $plannedEvent->recurringEvents()->where('closed', false)->orderBy('date')->list('id');
            // $this->event->attach($recurringIds);
            // $plannedEvent->detach($recurringIds);
        }
    }
}
