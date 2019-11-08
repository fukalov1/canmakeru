<template>
    <div>
        <ul class="paginator">
            <li class="no-border">
                <a @click="prevPage" v-if="page>1" class="pointer">
                    пред.
                </a>
            </li>
            <li v-for="p in countPage"  :class="{active: page===p}">
                <span @click="setPage(p)" class="pointer">
                    {{ p }}
                </span>
            </li>
            <li class="no-border">
                <a @click="nextPage" v-if="page<countPage" class="pointer">
                    след.
                </a>
            </li>
        </ul>


    </div>
</template>


<script>
    export default {
        name: 'paginator',
        props: {
            page: {
                type: Number,
                default: 1
            },
            perPage: {
                type: Number,
                default: 10
            },
            countPage: {
                type: Number,
                default: 0
            }
        },
        methods: {
            setPage(p){
                this.$emit('set-page', p);
            },
            nextPage(){
                this.$emit('set-next');
            },
            prevPage(){
                this.$emit('set-prev');
            }
        }
    }
</script>

<style scoped>
    .paginator li {
        padding: 3px;
        border: 1px solid #989898;
        display: inline-block;
        margin: 0 1px;
        min-width: 20px;
        text-align: center;

    }
    .paginator li.active {
        font-weight: bold;
        color: #0d6aad;
        border-color: #0d6aad;
    }
    .paginator li.no-border {
        border: none;
    }
    .pointer {
        cursor: pointer;
    }
</style>
