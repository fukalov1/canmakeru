<template>
    <div>
        Обновление токена
        <button v-if="!processing" type="button">запустить</button>
        <span class="h4" v-else>
            {{ message }}
        </span>
    </div>
</template>

<script>
    export default {
        name: 'yandex-refresh-token',
        data() {
            return {
                processing: false,
                message: 'Идет обновления токена.'
            }
        },
        methods: {

        }
    }
</script>
