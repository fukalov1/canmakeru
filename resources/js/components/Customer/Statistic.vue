<template>
    <div class="small">
        <line-chart :chart-data="data" :height="300" :options="{responsive: true, maintainAspectRatio: true}"></line-chart>
    </div>
</template>

<script>
    import LineChart from './LineChart.js'

    export default {
        components: {
            LineChart
        },
        data () {
            return {
                data: null
            }
        },
        mounted () {
            this.fillData()
        },
        methods: {
            fillData () {
                this.data = {
                    labels: ['март','апрель','май'],
                    datasets: [
                        {
                            label: 'Data One',
                            backgroundColor: '#f87979',
                            data: [1500,230,3400]
                        }
                    ]
                }

                axios({
                    url: `/data/statistic`,
                    method: 'GET'
                })
                    .then(response => {
                        this.data = response.data;
                    })
                    .catch(error => {

                    });
            },

        }
    }
</script>

<style>
    .small {
        max-width: 1000px;
        margin:  150px auto;
    }
</style>
