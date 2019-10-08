<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Tour Code</th>
        <th>Tour Guide Name</th>
    </tr>
    </thead>
    <tbody>
    @foreach($departures as $departure)
        <tr>
            <td>{{ $departure->date }}</td>
            <td>{{ $departure->tour->info->tour_code }}</td>
            <td>{{ $departure->schedule->full_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>