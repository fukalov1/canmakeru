<!--<template>-->
<!--    <div>-->
<!--        <line-chart :height="300" :width="300"/>-->
<!--        <button @click="increase()">Increase height</button>-->
<!--    </div>-->
<!--</template>-->

<!--<script>-->

<!--    import LineChart from './ChartContainer';-->

<!--    export default {-->
<!--        components: { LineChart },-->
<!--        data () {-->
<!--            return {-->
<!--                height: 300-->
<!--            }-->
<!--        },-->
<!--        methods: {-->
<!--            increase () {-->
<!--                this.height += 10-->
<!--            }-->
<!--        },-->
<!--        computed: {-->
<!--            myStyles () {-->
<!--                return {-->
<!--                    height: `${this.height}px`,-->
<!--                    position: 'relative'-->
<!--                }-->
<!--            }-->
<!--        }-->
<!--    }-->
<!--</script>-->








<script>
    import VueCharts from 'vue-chartjs'
    import { Bar, Line } from 'vue-chartjs'


    export default {
        extends: Line,
        data() {
            return {
                data: [],
                labels: [],
                // height: 300,
            }
        },
        mounted () {
            // Overwriting base render method with actual data.
        },
        created() {
            this.getData();
        },
        computed: {
            myStyles () {
                return {
                    height: `${this.height}px`,
                    position: 'relative'
                }
            }
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
                        this.showChart();
                    })
                    .catch(error => {

                    });
            },
            showChart() {
                this.renderChart({
                    labels: this.labels,
                    datasets: [
                        {
                            label: 'Динамика поверок партнера',
                            backgroundColor: '#f87979',
                            data: this.data
                        }
                    ]
                });
            }
        }
    }

</script>

<style>
    .small {
        max-width: 600px;
        margin:  150px auto;
    }
</style>
