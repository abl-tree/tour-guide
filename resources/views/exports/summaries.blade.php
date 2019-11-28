<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Tour Code</th>
        <th>Tour Guide Name</th>
        <th>Voucher</th>
    </tr>
    </thead>
    <tbody>
    @foreach($departures as $departure)
        <tr>
            <td>{{ $departure->date }}</td>
            <td>{{ $departure->tour->info->tour_code }}</td>
            <td>{{ $departure->schedule ? $departure->schedule->full_name : 'Unassigned' }}</td>
            <td>{{ (($departure->complete_voucher !== 0) ? 'Complete' : (($departure->serial_numbers_count) ? 'Incomplete' : 'Empty' ) ) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>