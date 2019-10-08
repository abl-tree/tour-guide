@component('mail::message')
# Updates

Tour guides {{$data['guide'] === 'true' ? '' : 'missed'}} updates in {{$month}}. Please see attached file.
@endcomponent
