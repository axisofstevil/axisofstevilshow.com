---
noindex: true
permalink: 404.html
---
@php
    // The site channel's page at 404 (config 'not_found'): its title and body; the site's own words only without it.
    $missing = $page->not_found ? json_decode(json_encode($page->not_found), true) : null;
@endphp
@extends('_layouts.master')

@section('title', ($missing['title'] ?? 'No such call').' — The Axis of Stevil Show')

@section('body')
    <section class="plate record lost" data-snap>
        <h1 class="work-title">{{ $missing['title'] ?? 'No such call' }}</h1>
        @if ($missing && trim(strip_tags($missing['content'])) !== '')
            <div class="work-body">{!! $missing['content'] !!}</div>
        @else
        <div class="work-body"><p>The number you have dialed is not in service. <a href="/earnings-calls/">The calls</a> are all here.</p></div>
        @endif
        <p class="caption">Plate 0 · Not in service</p>
    </section>
@endsection
