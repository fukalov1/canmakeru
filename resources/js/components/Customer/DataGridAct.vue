<template>
    <div>
        <transition name="modal-fade" v-if="showModal">
            <div class="modal-backdrop">

                <div class="modal">

                    <div class="modal-header">
                        <slot name="header">
                            <div class="row">
                                <div class="col-md-10">
                                    {{ photo_title }}
<!--                                    <br/>-->
<!--                                    <a-->
<!--                                        class="header-link"-->
<!--                                        target="_blank"-->
<!--                                        :href="`https://fgis.gost.ru/fundmetrology/cm/results?filter_result_docnum=${current_protokol}`">-->
<!--                                        Посмотреть в Федеральном информационном фонде*-->
<!--                                    </a>-->
<!--                                    <p>* Внимание. Данные передаются в течении 60 дней</p>-->
                                </div>
                                <div class="col-md-2 text-right">
                                    <button class="btn btn-primary" @click="showModal=false">
                                        закрыть
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">

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
        </transition>

        <div class="row table-panel">
            <div class="col-md-12 text-right">
                найдено
                <span v-if="filtered">
                    {{ acts_.length }}
                </span>
                <span v-else>
                    {{ acts.length }}
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
                    :selectForward="true"
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
                    <th v-for="(item, index) in column_names">
                        {{ item }}
                        <span v-if="index<8">
                        <font-awesome-icon
                            icon="sort-up"
                            v-if="getType(index)==='desc'"
                            @click="setSort(index,'asc')"
                            class="mini-pointer"
                            title="отсортировать по возрастанию"/>
                        <font-awesome-icon
                            icon="sort-down"
                            v-if="getType(index)==='asc'"
                            @click="setSort(index,'desc')"
                            class="mini-pointer"
                            title="отсортировать по убыванию"/>
                        <font-awesome-icon
                            icon="sort"
                            v-if="getType(index)===null"
                            @click="setSort(index,'asc')"
                            class="mini-pointer"
                            title="сортировка"/>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="acts_.length===0">
                    <td class="text-center" colspan="6">
                        <h6>
                            Нет данных о поверках
                        </h6>
                    </td>
                </tr>
                <tr v-else v-for="item in acts_">
                    <td v-for="field in columns">
                        <span
                           v-if="field==='act_photo'">

                        <font-awesome-icon
                            class="pointer"
                            icon="file-image"
                            @click="showActPhoto(item)"
                            title="фото акта"
                        />
                            <font-awesome-icon
                                class="pointer"
                                icon="file"
                                @click="showAct(item)"
                                title="акт"
                            />
                            <font-awesome-icon
                                @click="exportPDF(item)"
                                class="pointer"
                                icon="file-pdf"
                                title="выгрузить результат поверки в PDF"
                            />
                        </span>
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

    import VueHotelDatepicker from '@northwalker/vue-hotel-datepicker'
    import Paginator from './Paginator.vue';

    import moment from 'moment'
    Vue.prototype.moment = moment

    export default {
        name: 'data-grid-act',
        components: {Paginator, VueHotelDatepicker},
        props: {
            customer_id: {
                type: Number,
                default: 0
            },
            acts: {
                type: Array,
                default: []
            },
        },
        data() {
            return {
                columns: ['number_act','date','miowner','type', 'address', 'act_photo'],
                column_names: ['Номер акта','Дата', 'Владелец', 'Тип', 'Примечание', ''],
                sort_columns: {fld: null, type: ''},
                data: [],
                page: 1,
                perPage: 10,
                acts_: this.acts,
                current_protokol: '',
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
            acts: {
                handler: function (val) {
                   this.getActs();
                },
                deep: true
            },
            sort_columns: {
                handler: function (val) {
                    this.setSortActs();
                },
                deep: true
            },
            page: function (val) {
                this.getActs();
            },
            perPage: function (val) {
                this.getActs();
            },
            word: function (val) {
                if(val.length==0) {
                    this.getActs();
                }
                else {
                    this.getFilterActs();
                }
            }
        },
        computed: {
            countPage() {
                let l = 0;
                let s = this.perPage;
                if (this.filtered)
                    l= this.acts_.length;
                else
                    l = this.acts.length;

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
            getActs() {
                this.acts_ = this.acts.filter((item, index) =>  {
                    return index>=(this.page*this.perPage-this.perPage) && index <=this.page*this.perPage-1;
                });
            },
            setSortActs() {
                if (this.sort_columns.fld !== null && this.sort_columns.type === 'asc') {
                    // console.log('sort data grid for',this.sort_columns[key], item);
                    let obj = this.acts_;
                    let item = this.columns[this.sort_columns.fld];
                    obj.sort((a, b) => (b[item] < a[item]) ? 1 : ((a[item] < b[item]) ? -1 : 0));
                    this.acts_ = obj;
                } else if (this.sort_columns.fld !== null && this.sort_columns.type === 'desc') {
                    // console.log('sort data grid for',this.sort_columns[key], item);
                    let obj = this.acts_;
                    let item = this.columns[this.sort_columns.fld];
                    obj.sort((a, b) => (a[item] < b[item]) ? 1 : ((b[item] < a[item]) ? -1 : 0));
                    this.acts_ = obj;
                }
            },
            getFilterActs() {
                this.acts_ = this.acts.filter((item, index) =>  {
                    let result = false;
                    let word = item.number_act+'';
                    if (word.includes(this.word) && this.word!='') {
                        result = true;
                    }
                    word = item.date+'';
                    if (word.includes(this.word) && this.word!='') {
                        result = true;
                    }
                    if (this.startDate && this.endDate) {
                        word = item.date+'';
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
                this.photo_title = `Свидетельство № ${item.protokol_num}, лицевая сторона`;
                let str = item.protokol_photo;
                let date = item.protokol_dt;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos/','');
                str = '/photo/'+ date + '/'+ str;
                this.photo = str;
                this.current_protokol = item.protokol_num;
            },
            showPhoto1(item) {
                this.showModal = true;
                this.photo_title = `Свидетельство № ${item.protokol_num} обратная сторона`;
                let str = item.protokol_photo1;
                let date = item.protokol_dt;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos/','');
                str = '/photo/'+ date+ '/' + str;
                this.photo = str;
                this.current_protokol = item.protokol_num;
            },
            showActPhoto(item) {
                this.showModal = true;
                this.photo_title = 'Акт № '+item.number_act+' от '+ item.date;
                let str = 'act_'+item.name + '.jpg';
                let date = item.date;
                date = date.slice(0,7);
                date = date.replace('-', '/');
                str = str.replace('photos/','');
                str = '/photo/'+ date+ '/' + str;
                this.photo = str;
            },
            showAct(item) {
                window.open(`/up?id=${item.number_act}&pin=${item.pin}`, '_blank');
            },
            exportPDF(item) {
                document.location = `/data/act/pdf?id=${item.number_act}&pin=${item.pin}`
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
                this.getFilterActs();
            },
            setReset() {
                this.startDate = null;
                this.endDate = null;
                this.getActs();
            },
            date: function (date) {
                return moment(date).format('YYYY-MM-DD');
            },
            moment: function (date) {
                return moment(date).format('MMMM Do YYYY, h:mm:ss a');
            },
            setSort(fld,type) {
                this.$set(this.sort_columns, 'fld', fld);
                this.$set(this.sort_columns, 'type', type);
            },
            getType(id) {
                if (this.sort_columns.fld===id)
                    return this.sort_columns.type;
                else
                    return null;
            },


        }
    }
</script>

<style scoped>
    .table-panel div span {
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
    .pointer {
        cursor: pointer;
        font-size: 25px;
        color: #3490dc;
    }
    .pointer:hover {
        color: #0d6aad;
        text-decoration: underline;
    }
    .mini-pointer {
        cursor: pointer;
        font-size: 15px;
        color: #3490dc;
    }
    .mini-pointer:hover {
        color: #0d6aad;
        text-decoration: underline;
    }

    .modal-backdrop {
        position: fixed;
        /*top: unset;*/
        /*bottom: 0;*/
        /*left: 0;*/
        /*right: 0;*/
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-backdrop {
        z-index: 99;
        opacity: 1;
    }

    .modal {
        background: #FFFFFF;
        box-shadow: 2px 2px 20px 1px;
        overflow-x: auto;
        display: flex;
        flex-direction: column;
        width: auto;
        height: auto;
        top: unset;
        left: auto;
    }

    .modal-header,
    .modal-footer {
        padding: 15px;
        display: flex;
    }

    .modal-header {
        text-align: center;
        font-size: 1.6rem;
        font-weight: 500;
    }

    .modal-footer {
        border-top: 1px solid #eeeeee;
        justify-content: flex-end;
    }

    .modal-body {
        position: relative;
        padding: 10px 20px;
        /*overflow: auto;*/
    }

    .modal-header .close {
        position: relative;
        right: 10px;
        top: -10px;
        /*padding: 1rem;*/
        /*margin: -1rem -1rem -1rem auto;*/
    }

    .close {
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
    }

    button.close {
        padding: 0;
        background-color: transparent;
        border: 0;
        -webkit-appearance: none;
    }

    .btn-danger {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }


    .modal-fade-enter,
    .modal-fade-leave-active {
        opacity: 0;
    }

    .modal-fade-enter-active,
    .modal-fade-leave-active {
        transition: opacity .5s ease
    }
    .header-link {
        font-size: 18px;
    }
    .modal-header p {
        margin: 0;
        padding: 0;
        font-size: 15px;
        color: red;
    }
</style>
