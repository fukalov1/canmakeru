<div class="col-lg-12">
    <div class="row">
        <div class="col-lg-12">
            <b>
                Yandex.Disk, Mb
            </b>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            Общий объем: {{ $total }}<br/>
            Использовано: {{ $used }}<br/>
            Доступно: {{ $free }}<br/>
        </div>
    </div>
</div>

<canvas id="myChart" width="400" height="400"></canvas>
<script>
    $(function () {
        var ctx = document.getElementById("myChart").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["Использовано (Гб)", "Доступно (Гб)"],
                datasets: [{
                    label: '# of Votes',
                    data: [{{ $used }}, {{ $free }}],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255,99,132,1)',
                        'rgba(54, 162, 235, 1)',
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
