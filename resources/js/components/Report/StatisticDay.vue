<template>
    <div class="small">
        <div class="row">
            <div class="col-md-4 text-right">
                Укажите период
            </div>
            <div class="col-md-4 text-left">
                <VueHotelDatepicker
                    placeholder="укажите период"
                    :monthList="monthList"
                    :weekList="weekList"
                    confirmText="Подтвердить"
                    resetText="Сбросить"
                    format="YYYY-MM-DD"
                    startDate="2018-10-01"
                    separator="-"
                    fromText="с"
                    toText="по"
                    v-on:confirm="setRange"
                    v-on:reset="setReset"
                    @check-in-changed="setRange"
                />
            </div>
            <div class="col-md-4">
            </div>
        </div>
        <line-chart :chart-data="data" :height="200" :options="{responsive: true, maintainAspectRatio: true}"></line-chart>
    </div>
</template>

<script>

    import LineChart from '../Customer/LineChart.js'
    import VueHotelDatepicker from '@northwalker/vue-hotel-datepicker'

    export default {
        name: 'statistic-day',
        components: {
            LineChart, VueHotelDatepicker
        },
        props: {
            customer_id: {
                type: Number,
                default: 0
            }
        },
        data () {
            return {
                data: null,
                monthList: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                weekList: ['Вс', 'Пн', 'Вт.', 'Ср.', 'Чт', 'Пт', 'Сб'],
                startDate: null,
                endDate: null
            }
        },
        mounted () {
            this.fillData()
        },
        watch: {
            startData: function (val) {
                this.fillData();
            }
        },
        methods: {
            fillData () {
                console.log('refresh data for statistic', this.customer_id);
                axios({
                    url: `/data/report_days`,
                    method: 'POST',
                    data: {
                        customer_id: this.customer_id,
                        start: this.startDate,
                        end: this.endDate
                    }
                })
                    .then(response => {
                        this.data = response.data;
                    })
                    .catch(error => {

                    });
            },
            setRange(val) {
                let str = val.start+'';
                this.startDate = str;
                str = val.end+'';
                this.endDate = str;
                this.fillData();

            },
            setReset() {
                this.data = null;
            },
        }
    }
</script>

<style>
    .small {
        max-width: 100%;
        max-height: 50vh;
    }
</style>
