@component('mail::message')
# Updates

Dear {{$guide->username}}, 
<p>Here's your tour assignment updates in {{$month}}.</p>

<div class="table" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box;">
    <table style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; margin: 30px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
        <thead style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box;">
            <tr>
                <th style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; border-bottom: 1px solid #edeff2; padding-bottom: 8px; margin: 0; text-align: center;">Date</th>
                <th style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; border-bottom: 1px solid #edeff2; padding-bottom: 8px; margin: 0; text-align: center;">Tour</th>
                <th style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; border-bottom: 1px solid #edeff2; padding-bottom: 8px; margin: 0; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box;">
            @foreach($schedules as $key => $schedule)
            <tr>
                <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0; text-align: center;">{{$schedule->departure ? $schedule->departure->date : null}}</td>
                <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0; text-align: center;">{{$schedule->departure && $schedule->departure->tour ? $schedule->departure->tour->title : null}}</td>
                <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0; text-align: center;">Confirmed</td>
            </tr>
            @endforeach

            @if(!count($schedules)) 
                <tr>
                    <td colspan=3 style="text-align: center;">No available schedule</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<p>PS: Please do not reply to this automatic email, in case of problem send a message to onceinrometours@gmail.com</p>

Kind Regard,<br>
Once in Rome Team
@endcomponent
