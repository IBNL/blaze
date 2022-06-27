<!DOCTYPE html>
<html lang="en">
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Double results</title>
</head>
<body>
    <table class="table table-hover table-bordered">
        <tbody>
            <tr>
              </tr>
                @for ($i = 0; $i <= 23; $i++)
                    <tr>
                        <th scope="row">{{$i}}:00 ~ {{$i}}:59</th>
                        @foreach ($resultByDay as $item)
                            @if($item['hour_by_created_at'] == $i && $item['minute_by_created_at'] <= 59)
                                <td scope="col" style ="background-color:{{$item['color_name']}};border-color: "></td>
                            @endif
                        @endforeach
                    </tr>
                @endfor
        </tbody>
    </table>
</body>
</html>