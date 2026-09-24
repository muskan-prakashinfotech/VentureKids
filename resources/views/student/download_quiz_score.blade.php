<style type="text/css">  
    table td, table th{  
        border:1px solid black;
        padding: 5px;  
    }  
</style>  
<div class="container">
    @if($quizDetail)
        <caption><h1>{{ $student_name}}</h1></caption>
        <table>
            <thead>
                <tr>
                    @if($quizType == 'content')
                    <th>Level Name</th>    
                    <th>Session Name</th>
                    @endif
                    <th>Quiz Name</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quizDetail as $quiz)
                    <tr>
                        @if($quizType == 'content')
                        <td>{{ $quiz['level'] }}</td>
                        <td>{{ $quiz['session'] }}</td>
                        @endif
                        <td>{{ $quiz['title'] }}</td>
                        <td>{{ $quiz['score'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        No data found
    @endif     
</div>