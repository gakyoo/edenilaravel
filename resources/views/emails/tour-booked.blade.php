<x-mail::message>
# Tour Request Received 🗓️

Hi {{ $tour->name }},

We've received your tour request for **{{ $tour->property?->title ?? 'the property' }}**.

**Your request details:**
- **Date:** {{ $tour->preferred_date?->format('l, F j, Y') }}
- **Time:** {{ $tour->preferred_time }}
@if($tour->message)
- **Message:** {{ $tour->message }}
@endif

Our team will confirm your visit shortly by phone or WhatsApp.

Thanks for choosing **Edenire.co.tz**!

<x-mail::button :url="url('/properties/'.($tour->property_id ?? ''))">
View Property
</x-mail::button>

— Edeni Realtors, Arusha
</x-mail::message>
