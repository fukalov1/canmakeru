<template>
    <div class="small">
        <line-chart :chart-data="data" :height="100" :options="{responsive: true, maintainAspectRatio: true}"></line-chart>
    </div>
</template>

<script>
    import LineChart from './LineChart.js'

    export default {
        components: {
            LineChart
        },
        props: {
            customer_id: {
                type: Number,
                default: 0
            }
        },
        data () {
            return {
                data: null
            }
        },
        mounted () {
            this.fillData()
        },
        watch: {
            customer_id: function (val) {
                this.fillData();
            }
        },
        methods: {
            fillData () {
                axios({
                    url: `/data/statistic`,
                    method: 'POST',
                    data: {customer_id: this.customer_id}
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
    }
</style>
