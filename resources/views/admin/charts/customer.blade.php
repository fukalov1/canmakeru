<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="col-lg-12">

    <div class="row">
        <div class="col-lg-12">
            <canvas id="myChart1" width="800" height="400"></canvas>
            <script>
                $(function () {
                    var ctx = document.getElementById("myChart1").getContext('2d');
                    var myChart1 = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [
                                @foreach($dinamic as $item)
                                    '{{ $item['date'] }}',
                                @endforeach
                            ],
                            datasets: [{
                                label: 'Динамика поверки счетчиков',
                                data: [
                                    @foreach($dinamic as $item)
                                    {{ $item['count'] }},
                                    @endforeach
                                ],
                                backgroundColor: [
                                    @foreach($dinamic as $item)
                                        'rgba(54, 162, 235, 0.2)',
                                    @endforeach
                                ],
                                borderColor: [
                                    @foreach($dinamic as $item)
                                        'rgba(255,99,132,1)',
                                    @endforeach
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero:true
                                    }
                                }]
                            }
                        }
                    });
                });
            </script>
        </div>
    </div>

</div>
