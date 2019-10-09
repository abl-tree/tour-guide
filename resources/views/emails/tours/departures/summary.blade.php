@component('mail::message')
# Updates

Tour guides {{$data['guide'] ? '' : 'missed'}} updates in {{$month}}. Please see attached file.
@endcomponent
