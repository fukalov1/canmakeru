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
                <a class="nav-link" id="act-tab" data-toggle="tab" href="#act" role="tab" aria-controls="act" aria-selected="false">Акты</a>
            </li>
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
            <div class="tab-pane fade show active" id="act" role="tabpanel" aria-labelledby="act-tab">
                <div v-if="act_progress" class="row">
                    <div class="col-12">
                        <h3>
                            Идет загрузка данных. Пожалуйста, ожидайте...
                        </h3>
                    </div>
                </div>
                <data-grid-act :customer_id="customer_id" :acts="this.acts" v-if="acts.length>0" v-else/>
            </div>
            <div class="tab-pane fade" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div v-if="progress" class="row">
                    <div class="col-12">
                        <h3>
                            Идет загрузка данных. Пожалуйста, ожидайте...
                        </h3>
                    </div>
                </div>
                <data-grid :customer_id="customer_id" :protokols="this.protokols" v-if="protokols.length>0" v-else/>
            </div>
            <div class="tab-pane fade" id="statistic" role="tabpanel" aria-labelledby="statistic-tab">
                <div class="statistic">
                    <statistic :customer_id="customer_id"/>
                </div>
            </div>
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <profile-user/>
            </div>
            <div class="tab-pane fade" id="report" role="tabpanel" aria-labelledby="report-tab">
                <statistic-day :customer_id="customer_id"/>
            </div>
        </div>
    </div>
</template>

<script>

    import DataGrid from "./DataGrid";
    import Statistic from "./Statistic";
    import StatisticDay from "../Report/StatisticDay";
    import ProfileUser from "./ProfileUser";
    import DataGridAct from "./DataGridAct";

    export default {
        components: {
            DataGrid, DataGridAct, Statistic, StatisticDay, ProfileUser
        },
        data() {
            return {
                customer_id: 0,
                data: [],
                acts: [],
                protokols: [],
                act_progress: false,
                progress: false
            }
        },
        mounted() {
            this.getAuth();
        },
        created() {
        },
        watch: {
            customer_id: function (val) {
                this.getActs();
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
                        this.getActs();
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
            getActs() {
                this.act_progress = true;
                axios({
                    url: `/data/acts`,
                    method: 'POST',
                    data: {customer_id: this.customer_id}
                })
                    .then(response => {
                        this.act_progress = false;
                        this.acts = response.data;
                    })
                    .catch(error => {
                        this.act_progress = false;
                    });
            },
            getProtokols() {
                this.progress = true;
                axios({
                    url: `/data/protokols`,
                    method: 'POST',
                    data: {customer_id: this.customer_id}
                })
                    .then(response => {
                        this.progress = false;
                        this.protokols = response.data.data;
                    })
                    .catch(error => {
                        this.progress = false;
                    });
            },
        }
    }
</script>

<style scope>
    h3 {
        text-align: center;
        font-size: 25px;
        font-weight: 700;
        padding: 30px 0;
    }
</style>
