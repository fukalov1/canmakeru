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
            <div class="col-md-1 text-left">
                Показывать по
            </div>
            <div class="col-md-1 text-left">
                    <select v-model="perPage" class="form-control">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
            </div>
            <div class="col-md-2 text-left">
                записей
            </div>
            <div class="col-md-4 text-left">
                <VueHotelDatepicker
                    placeholder="укажите период"
                    :monthList="monthList"
                    :weekList="weekList"
                    confirmText="Подтвердить"
                    resetText="Сбросить"
                    format="YYYY-MM-DD"
                    separator="-"
                    fromText="с"
                    toText="по"
                    v-on:confirm="setRange"
                    :startDate="startDate"
                    :endtDate="endDate"
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
                <tr v-for="item in protokols">
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
                <paginator
                    :page="page"
                    :countPage="countPage"
                    :perPage="perPage"
                    v-on:set-page="setPage"
                    v-on:set-next="setNext"
                    v-on:set-prev="setPrev"/>
            </div>
        </div>


    </div>
</template>


<script>

    import VueHotelDatepicker from '@northwalker/vue-hotel-datepicker'
    import Paginator from './Paginator.vue';

    export default {
        name: 'data-grid',
        components: {Paginator, VueHotelDatepicker},
        data() {
            return {
                columns: ['protokol_num','protokol_dt','pin','protokol_photo','protokol_photo1','meter_photo'],
                column_names: ['Номер св-ва','Дата поверки','Пин-код','Св-во лиц.','Св-во обр.','Счетчик'],
                data: [],
                page: 1,
                perPage: 10,
                protokols: [],
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
        watch: {
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
            countPage(){
                let l = this.data.length,
                s = this.perPage;
                return Math.ceil(l/s);
            }
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                axios({
                    url: `/data/protokols`,
                    method: 'GET'
                })
                    .then(response => {
                        this.data = response.data.data;
                        this.getProtokols();
                    })
                    .catch(error => {

                    });
            },
            getProtokols() {
                this.protokols = this.data.filter((item, index) =>  {
                    return index>=(this.page*this.perPage-this.perPage+1) && index <=this.page*this.perPage;
                });
            },
            getFilterProtokols() {
                this.protokols = this.data.filter((item, index) =>  {
                    let result = false;
                    let word = item.protokol_num+'';
                    if (word.includes(this.word))
                        result = true;
                    word = item.protokol_dt+'';
                    if (word.includes(this.word))
                        result = true;
                    if (this.startDate && this.endtDate) {
                        word = item.protokol_dt+'';
                        if (word>this.startDate && word<this.endtDate)
                            result = true
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
                str = str.replace('photos','/photo/'+date);
                this.photo = str;
            },
            showPhoto1(item) {
                this.showModal = true;
                this.photo_title = 'Свидетельство, обратная сторона';
                let str = item.protokol_photo1;
                let date = item.protokol_dt;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos','/photo/'+date);
                this.photo = str;
            },
            showMeter(item) {
                this.showModal = true;
                this.photo_title = 'Счетчик';
                let str = item.meter_photo;
                let date = item.protokol_dt;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos','/photo/'+date);
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
                this.startDate = val.start;
                this.endtDate = val.end;
                this.getFilterProtokols();
            }
        }
    }
</script>

<style scoped>
    .table-panel div {
        font-size: 12px;
        line-height: 35px;
    }
    .table-panel select, .table-panel input {
        font-size: 12px;
    }
    .photo {
        height: 80vh;
    }
</style>
