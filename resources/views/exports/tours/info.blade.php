<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Tour Code</th>
    </tr>
    </thead>
    <tbody>
    @foreach($departures as $departure)
        <tr>
            <td>{{ $departure->date }}</td>
            <td>{{ $departure->tour->info->tour_code }}</td>
        </tr>
    @endforeach
    </tbody>
</table>