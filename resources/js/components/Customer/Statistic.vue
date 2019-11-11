<template>
    <div class="container">
        <div class="row">
            <div class="col-sd-12">
                <line-chart :chart-data="dataChart" :height="300" :options="{responsible: true, maintainAspectRatio: true}"/>
            </div>
        </div>
    </div>
</template>

<script>
    import LineChart from "./LineChart.js";

    export default {
       components: { LineChart},
       data() {
            return {
                data: [],
                labels: [],
                dataChart: {'labels':[], 'datasets': []}
            }
        },
        mounted () {
            this.getData();
        },
        computed: {

        },
        methods: {
            getData() {
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
                        this.dataChart.labels = this.labels;
                        this.dataChart.datasets = this.data;
                    })
                    .catch(error => {

                    });
            },
        }
    }

</script>

<style>
    .small {
        max-width: 600px;
        margin:  150px auto;
    }
</style>
