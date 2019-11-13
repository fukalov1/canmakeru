<template>
    <div>
        <transition name="modal" v-if="showModal">
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">

                        <div class="modal-header">
                            <slot name="header">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{ photo_title }}
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button class="modal-default-button" @click="showModal=false">
                                            закрыть
                                        </button>
                                    </div>
                                </div>

                            </slot>
                        </div>

                        <div class="modal-body">
                            <slot name="body">
                                <img :src="photo" class="photo"/>
                            </slot>
                        </div>

<!--                        <div class="modal-footer">-->
<!--                            <slot name="footer">-->
<!--                                <button class="modal-default-button" @click="showModal=false">-->
<!--                                    OK-->
<!--                                </button>-->
<!--                            </slot>-->
<!--                        </div>-->
                    </div>
                </div>
            </div>
        </transition>

        <div class="row table-panel">
            <div class="col-md-12 text-right">
                найдено
                <span v-if="filtered">
                    {{ protokols_.length }}
                </span>
                <span v-else>
                    {{ protokols.length }}
                </span>
                записей
            </div>
        </div>
        <div class="row table-panel">
            <div class="col-md-1 text-left">
                <span  v-show="filtered==false">
                    Показывать по
                </span>
            </div>
            <div class="col-md-1 text-left">
                    <select v-model="perPage" class="form-control" v-show="filtered==false">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
            </div>
            <div class="col-md-2 text-left">
                <span  v-show="filtered==false">
                    записей
                </span>
            </div>
            <div class="col-md-4 text-left">
                <VueHotelDatepicker
                    placeholder="укажите период"
                    :monthList="monthList"
                    :weekList="weekList"
                    confirmText="Подтвердить"
                    resetText="Сбросить"
                    format="YYYY-MM-DD"
                    :selectForward="false"
                    :startDate="date(Date.now())"
                    minDate="2018-10-01"
                    separator="-"
                    fromText="с"
                    toText="по"
                    v-on:confirm="setRange"
                    v-on:reset="setReset"
                    @check-in-changed="setRange"
                />
            </div>
            <div class="col-md-2 text-right">

            </div>
            <div class="col-md-2 text-left">
            <input type="text" v-model="word" placeholder="поиск по номеру" class="form-control">
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th v-for="item in column_names">
                        {{ item }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in protokols_">
                    <td v-for="field in columns">
                        <a href="#"
                           @click="showPhoto(item)"
                           v-if="field==='protokol_photo'">
                            {{ item[field] }}
                        </a>
                        <a href="#"
                           @click="showPhoto1(item)"
                           v-else-if="field==='protokol_photo1'">
                            {{ item[field] }}
                        </a>
                        <a href="#"
                           @click="showMeter(item)"
                           v-else-if="field==='meter_photo'">
                            {{ item[field] }}
                        </a>
                        <span v-else>
                            {{ item[field] }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-md-12 text-center">
                <paginator  v-show="filtered==false"
                    :page="page"
                    :countPage="countPage"
                    :perPage="perPage"
                    v-on:set-page="setPage"
                    v-on:set-next="setNext"
                    v-on:set-prev="setPrev"
                />
            </div>
        </div>


    </div>
</template>


<script>

    // import {eventBus} from '../../app.js'
    import VueHotelDatepicker from '@northwalker/vue-hotel-datepicker'
    import Paginator from './Paginator.vue';

    // Vue.use(require('vue-moment'));
    import moment from 'moment'
    Vue.prototype.moment = moment

    export default {
        name: 'data-grid',
        components: {Paginator, VueHotelDatepicker},
        props: {
            customer_id: {
                type: Number,
                default: 0
            },
            protokols: {
                type: Array,
                default: []
            }
        },
        data() {
            return {
                columns: ['protokol_num','protokol_dt','pin','protokol_photo','protokol_photo1','meter_photo'],
                column_names: ['Номер св-ва','Дата поверки','Пин-код','Св-во лиц.','Св-во обр.','Счетчик'],
                data: [],
                page: 1,
                perPage: 10,
                protokols_: this.protokols,
                word: '',
                showModal: false,
                photo_title: '',
                photo: '',
                photo1: '',
                meter: '',
                monthList: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                weekList: ['Вс', 'Пн', 'Вт.', 'Ср.', 'Чт', 'Пт', 'Сб'],
                startDate: null,
                endDate: null
            }
        },
        mounted() {
        },
        watch: {
            protokols: {
                handler: function (val) {
                   this.getProtokols();
                },
                deep: true
            },
            page: function (val) {
                this.getProtokols();
            },
            perPage: function (val) {
                this.getProtokols();
            },
            word: function (val) {
                if(val.length==0) {
                    this.getProtokols();
                }
                else {
                    this.getFilterProtokols();
                }
            }
        },
        computed: {
            countPage() {
                let l = 0;
                let s = this.perPage;
                if (this.filtered)
                    l= this.protokols_.length;
                else
                    l = this.protokols.length;

                return Math.ceil(l/s);
            },
            filtered() {
                if (this.word || this.startDate || this.endDate)
                    return true;
                else
                    return false;
            }
        },
        methods: {
            getProtokols() {
                this.protokols_ = this.protokols.filter((item, index) =>  {
                    return index>=(this.page*this.perPage-this.perPage) && index <=this.page*this.perPage;
                });
            },
            getFilterProtokols() {
                this.protokols_ = this.protokols.filter((item, index) =>  {
                    let result = false;
                    let word = item.protokol_num+'';
                    if (word.includes(this.word) && this.word!='') {
                        result = true;
                    }
                    word = item.protokol_dt+'';
                    if (word.includes(this.word) && this.word!='') {
                        result = true;
                    }
                    if (this.startDate && this.endDate) {
                        word = item.protokol_dt+'';
                        word = word.slice(0,10);
                        // word = word.replace('-','');
                        if (word>=this.startDate && word<=this.endDate) {
                            result = true;
                        }
                    }

                    return result;
                });
            },
            showPhoto(item) {
                this.showModal = true;
                this.photo_title = 'Свидетельство, лицевая сторона';
                let str = item.protokol_photo;
                let date = item.protokol_dt;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos','/photo/');
                str = '/photo/'+ date + '/'+ str;
                this.photo = str;
            },
            showPhoto1(item) {
                this.showModal = true;
                this.photo_title = 'Свидетельство, обратная сторона';
                let str = item.protokol_photo1;
                let date = item.protokol_dt;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos','/photo/');
                str = '/photo/'+ date+ '/' + str;
                this.photo = str;
            },
            showMeter(item) {
                this.showModal = true;
                this.photo_title = 'Счетчик';
                let str = item.meter_photo;
                let date = item.protokol_dt;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos','/photo/');
                str = '/photo/'+ date+ '/' + str;
                this.photo = str;
            },
            setPage(page) {
                this.page = page;
            },
            setPrev() {
                this.page = this.page - 1;
            },
            setNext() {
                this.page = this.page + 1;
            },
            setRange(val) {
                console.log(val.start, val.end);
                let str = val.start+'';
                // str = str.replace('-','');
                this.startDate = str;
                str = val.end+'';
                // str = str.replace('-','');
                this.endDate = str;
                this.getFilterProtokols();
            },
            setReset() {
                this.startDate = null;
                this.endDate = null;
                this.getProtokols();
            },
            date: function (date) {
                return moment(date).format('YYYY-MM-DD');
            },
            moment: function (date) {
                return moment(date).format('MMMM Do YYYY, h:mm:ss a');
            }
        }
    }
</script>

<style scoped>
    .table-panel div {
        line-height: 50px;
    }
    .table-panel select, .table-panel input {
        padding: 8px;
        border: 1px solid #eee;
        color: #505050;
        font-size: 16px;
        line-height: 32px;
        outline: none;
        height: 100%;
        border-radius: 0;
    }
    .photo {
        height: 80vh;
    }
</style>
