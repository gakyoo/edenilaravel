<?php

namespace App\Mail;

use App\Models\Tour;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TourStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Tour $tour) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tour '.ucfirst($this->tour->status).' — '.($this->tour->property?->title ?? 'Property'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tour-status-changed',
        );
    }
}
