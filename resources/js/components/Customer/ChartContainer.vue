<template>
    <div class="container">
        <line-chart
            v-if="loaded"
            :chartdata="chartdata"
            :options="options"/>
    </div>
</template>

<script>
    import LineChart from './Chart.vue'

    export default {
        name: 'LineChartContainer',
        components: { LineChart },
        data: () => ({
            loaded: false,
            chartdata: null
        }),
        async mounted () {
            this.loaded = false
            try {
                debugger
                axios({
                    url: `/data/statistic`,
                    method: 'GET'
                })
                    .then(response => {
                        let data = response.data;
                        data.forEach((item) => {
                            this.labels.push(item.date);
                            this.data.push(item.count);
                        });

                        let chartdata = {'labels': this.labels, 'datasets': []};

                        chartdata.datasets = [
                            {
                                'label': 'Коммиты на GitHub',
                                'backgroundColor': '#f87979',
                                'data': this.data
                            }
                        ];
                        debugger
                        this.chartdata = chartdata;

                        this.loaded = true

                        // this.showChart();
                    })
                    .catch(error => {

                    });


                // let statistic = await fetch('/data/statistic');
            } catch (e) {
                console.error(e)
            }
        }
    }
</script>
