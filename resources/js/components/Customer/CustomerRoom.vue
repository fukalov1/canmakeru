<template>
    <div>
        <h4>
            Личный кабинет партнера
        </h4>
        <div class="row">
            <div class="col-md-9">
            </div>
            <div class="col-md-1 text-right">
                Работник
            </div>
            <div class="col-md-2 text-right">
                <select class="form-control" v-model="customer_id">
                    <option v-for="(item,index) in data" :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
        </div>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Список поверок</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Профиль</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="stat-tab" data-toggle="tab" href="#statistic" role="tab" aria-controls="statisttic" aria-selected="false">Статистика</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="report-tab" data-toggle="tab" href="#report" role="tab" aria-controls="report" aria-selected="false">Динамика поверок за период</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <data-grid :customer_id="customer_id" :protokols="this.protokols" v-if="protokols.length>0"/>
            </div>
            <div class="tab-pane fade" id="statistic" role="tabpanel" aria-labelledby="statistic-tab">
                <div class="statistic">
                    <statistic :customer_id="customer_id"/>
                </div>
            </div>
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <profile-user/>
            </div>
            <div class="tab-pane fade" id="report" role="tabpanel" aria-labelledby="report-tab"></div>
        </div>
    </div>
</template>

<script>

    import DataGrid from "./DataGrid";
    import Statistic from "./Statistic";
    import {eventBus} from '../../app.js'

    export default {
        components: {
            DataGrid, Statistic
        },
        data() {
            return {
                customer_id: 0,
                data: [],
                protokols: []
            }
        },
        mounted() {
            this.getAuth();
        },
        created() {
            eventBus.$on('update-protokols', () => {
                console.log('call update-protokols', this.protokols.length);
            })
        },
        watch: {
            customer_id: function (val) {
                this.getProtokols();
            }
        },
        methods: {
            getAuth() {
                axios({
                    url: `/data/auth_user`,
                    method: 'GET'
                })
                    .then(response => {
                        this.customer_id = response.data.customer_id;
                        this.loadData();
                        this.getProtokols();
                    })
                    .catch(error => {

                    });
            },
            loadData() {
                axios({
                    url: `/data/workers`,
                    method: 'GET'
                })
                    .then(response => {
                        this.data = response.data;
                    })
                    .catch(error => {

                    });
            },
            getProtokols() {
                axios({
                    url: `/data/protokols`,
                    method: 'POST',
                    data: {customer_id: this.customer_id}
                })
                    .then(response => {
                        this.protokols = response.data.data;
                    })
                    .catch(error => {

                    });
            },
        }
    }
</script>
