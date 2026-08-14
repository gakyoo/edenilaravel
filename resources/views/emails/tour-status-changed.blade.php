<x-mail::message>
# Your Tour is {{ ucfirst($tour->status) }}

Hi {{ $tour->name }},

Your tour request for **{{ $tour->property?->title ?? 'the property' }}** has been updated to: **{{ $tour->status }}**.

**Details:**
- **Date:** {{ $tour->preferred_date?->format('l, F j, Y') }}
- **Time:** {{ $tour->preferred_time }}

@if($tour->status === 'confirmed')
Great news — your visit is confirmed! Please arrive a few minutes early.
@elseif($tour->status === 'cancelled')
We're sorry this visit couldn't go ahead. Feel free to request another time.
@elseif($tour->status === 'completed')
Thank you for visiting! We hope you found what you were looking for.
@endif

Questions? Reply to this email or reach us on WhatsApp.

— Edeni Realtors, Edenire.co.tz
</x-mail::message>
