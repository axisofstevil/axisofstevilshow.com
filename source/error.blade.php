---
noindex: true
permalink: error.html
---
@php
    // The channel's page at error (config 'error'): its title and body say everything; the site's own words only
    // without it. The file a host serves for a request it could not answer.
    $error = $page->error ? json_decode(json_encode($page->error), true) : null;
@endphp
@extends('_layouts.master')

@section('title', ($error['title'] ?? 'Technical difficulties').' — The Axis of Stevil Show')

@section('body')
    <section class="plate record lost" data-snap>
        <h1 class="work-title">{{ $error['title'] ?? 'Technical difficulties' }}</h1>
        @if ($error && trim(strip_tags($error['content'])) !== '')
            <div class="work-body">{!! $error['content'] !!}</div>
        @else
            <div class="work-body"><p>The call has dropped. Management is aware and thanks you for holding. Try again in a moment, or go back to <a href="/earnings-calls/">the calls</a>.</p></div>
        @endif
        <p class="caption">Plate 0 · Technical difficulties</p>
    </section>
@endsection
