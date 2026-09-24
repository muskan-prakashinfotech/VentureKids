<table class="table table-bordered">
    <tr>
        <th>Feedback</th>
        <th>Grade</th>
        <th>Date</th>
    </tr>
    @forelse($studentcomminucate as $row)
        <tr>
            <td>{{ $row->feedback }}</td>
            <td>{{ $row->grade }}</td>
            <td>
                @php
                    $date = date('d-m-Y', strtotime($row->created_at));
                @endphp
                {{ $date }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" align="center">Grade data not found</td>
        </tr>
    @endforelse
</table>